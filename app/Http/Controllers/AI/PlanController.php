<?php
// app/Http/Controllers/AI/PlanController.php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\AiTool;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\License;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $plans = Plan::with('tool')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Plans/Index', [
            'plans' => $plans  // ← Now passing 'plans'
        ]);
    }

    public function publicPricing()
    {
        $tools = AiTool::with(['plans' => function($query) {
            $query->where('is_active', true)->orderBy('price');
        }])->where('is_active', true)->get();

        return Inertia::render('Pricing', [
            'tools' => $tools
        ]);
    }

    public function toggleStatus(Plan $plan)
    {
        $plan->is_active = !$plan->is_active;
        $plan->save();

        return response()->json(['is_active' => $plan->is_active]);
    }

    public function create()
    {
        $tools = AiTool::where('is_active', true)->get();
        return Inertia::render('Plans/Create', [
            'tools' => $tools
        ]);
    }

    // app/Http/Controllers/AI/PlanController.php

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tool_id' => 'required|exists:ai_tools,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            // 'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime,one_time',
            'duration_days' => 'nullable|integer|min:1',
            'device_limit' => 'nullable|integer|min:1',  // Changed from licenses_count
            'api_call_limit' => 'nullable|integer|min:0',
            'concurrent_users' => 'nullable|integer|min:1',
            'feature_flags' => 'nullable|array',
            'limitations' => 'nullable|array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer'
        ]);

        // Set defaults for nullable fields
        $validated['device_limit'] = $validated['device_limit'] ?? 1;
        $validated['concurrent_users'] = $validated['concurrent_users'] ?? 1;
        $validated['currency'] = $validated['currency'] ?? 'USD';
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Plan::create($validated);

        return redirect()->route('plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'tool_id' => 'required|exists:ai_tools,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly,lifetime,one_time',
            'duration_days' => 'nullable|integer|min:1',
            'device_limit' => 'nullable|integer|min:1',
            'api_call_limit' => 'nullable|integer|min:0',
            'concurrent_users' => 'nullable|integer|min:1',
            'feature_flags' => 'nullable|array',
            'limitations' => 'nullable|array',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer'
        ]);

        $plan->update($validated);

        return redirect()->route('plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function edit(Plan $plan)
    {
        $tools = AiTool::where('is_active', true)->get();

        return Inertia::render('Plans/Edit', [
            'plan' => $plan,
            'tools' => $tools
        ]);
    }



    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('plans.index')
            ->with('success', 'Plan deleted successfully.');
    }


    public function show(Plan $plan)
    {
        $plan->load(['tool' => function($query) {
            $query->withCount('plans');
        }]);

        return Inertia::render('Plans/Show', [
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => $plan->price,
                'currency' => $plan->currency,
                'billing_cycle' => $plan->billing_cycle,
                'duration_days' => $plan->duration_days,
                'device_limit' => $plan->device_limit,
                'api_call_limit' => $plan->api_call_limit,
                'concurrent_users' => $plan->concurrent_users,
                'features' => $plan->features ?? [],
                'limitations' => $plan->limitations ?? [],
                'is_popular' => $plan->is_popular,
                'is_active' => $plan->is_active,
                'metadata' => $plan->metadata ?? [],
                'tool_id' => $plan->tool_id,
                'tool' => $plan->tool ? [
                    'id' => $plan->tool->id,
                    'name' => $plan->tool->name,
                    'description' => $plan->tool->description,
                    'version' => $plan->tool->version,
                    'metadata' => $plan->tool->metadata ?? [],
                    'plans_count' => $plan->tool->plans_count ?? 0
                ] : null
            ]
        ]);
    }

    public function createStripeCheckout(Request $request, Plan $plan)
    {
        $request->validate([
            'package_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:stripe',
        ]);

        if (!$plan->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This plan is not available.'
            ], 422);
        }

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login before subscribing.'
            ], 401);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $amount = (int) round($plan->price * 100);

        if ($amount < 50) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid plan price for Stripe checkout.'
            ], 422);
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',

            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($plan->currency ?? 'usd'),
                    'product_data' => [
                        'name' => $plan->tool?->name . ' - ' . $plan->name . ' Plan',
                        'description' => $plan->description ?: 'Subscription purchase',
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],

            'metadata' => [
                'plan_id' => (string) $plan->id,
                'tool_id' => (string) $plan->tool_id,
                'user_id' => (string) Auth::id(),
                'package_name' => $request->package_name ?? $plan->name,
            ],

            'success_url' => route('plans.stripe.success', $plan->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('plans.stripe.cancel', $plan->id),
        ]);

        return response()->json([
            'success' => true,
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ]);
    }



    // public function stripeSuccess(Request $request, Plan $plan)
    // {
    //     $sessionId = $request->get('session_id');

    //     if (!$sessionId) {
    //         return redirect()->route('plans.show', $plan->id)
    //             ->with('error', 'Stripe session ID missing.');
    //     }

    //     Stripe::setApiKey(env('STRIPE_SECRET'));

    //     try {
    //         $session = Session::retrieve($sessionId);

    //         // Make sure payment is completed
    //         if ($session->payment_status !== 'paid') {
    //             return redirect()->route('plans.show', $plan->id)
    //                 ->with('error', 'Payment not completed.');
    //         }

    //         // Prevent duplicate PAYMENT creation if page refreshes
    //         $existingPayment = Payment::where('gateway_reference_id', $session->id)->first();

    //         if ($existingPayment) {
    //             return redirect()->route('plans.show', $plan->id)
    //                 ->with('success', 'Payment already processed.');
    //         }

    //         // Get user from metadata (or fallback to current logged-in user)
    //         $userId = $session->metadata->user_id ?? Auth::id();

    //         if (!$userId || $userId === 'guest') {
    //             return redirect()->route('plans.show', $plan->id)
    //                 ->with('error', 'Unable to identify user for payment/license creation.');
    //         }

    //         $startsAt = now();
    //         $expiresAt = null;

    //         // Calculate expiry based on billing cycle or duration_days
    //         if (!empty($plan->duration_days)) {
    //             $expiresAt = now()->addDays($plan->duration_days);
    //         } else {
    //             switch ($plan->billing_cycle) {
    //                 case 'monthly':
    //                     $expiresAt = now()->addMonth();
    //                     break;
    //                 case 'quarterly':
    //                     $expiresAt = now()->addMonths(3);
    //                     break;
    //                 case 'yearly':
    //                     $expiresAt = now()->addYear();
    //                     break;
    //                 case 'lifetime':
    //                 case 'one_time':
    //                 default:
    //                     $expiresAt = null;
    //                     break;
    //             }
    //         }

    //         // Prevent duplicate LICENSE creation
    //         $existingLicense = License::where('metadata->stripe_session_id', $session->id)->first();

    //         if (!$existingLicense) {
    //             $license = License::create([
    //                 'user_id' => $userId,
    //                 'tool_id' => $plan->tool_id,
    //                 'plan_id' => $plan->id,
    //                 'license_key' => strtoupper('LIC-' . Str::random(8) . '-' . Str::random(8)),
    //                 'package_name' => $session->metadata->package_name ?? $plan->name,
    //                 'bundle_id' => null,
    //                 'domain' => null,
    //                 'environment' => 'production',
    //                 'allowed_domains' => [],
    //                 'allowed_ips' => [],
    //                 'device_ids' => [],
    //                 'device_count' => 0,
    //                 'api_calls_used' => 0,
    //                 'api_calls_limit' => $plan->api_call_limit,
    //                 'starts_at' => $startsAt,
    //                 'expires_at' => $expiresAt,
    //                 'last_verified_at' => null,
    //                 'last_used_at' => null,
    //                 'status' => 'active',
    //                 'metadata' => [
    //                     'payment_method' => 'stripe',
    //                     'stripe_session_id' => $session->id,
    //                     'stripe_payment_intent' => $session->payment_intent ?? null,
    //                     'stripe_customer' => $session->customer ?? null,
    //                     'amount_total' => $session->amount_total ?? null,
    //                     'currency' => strtoupper($session->currency ?? $plan->currency ?? 'USD'),
    //                 ],
    //                 'is_trial' => false,
    //                 'auto_renew' => true,
    //                 'notes' => 'Created automatically after successful Stripe payment.',
    //             ]);
    //         } else {
    //             $license = $existingLicense;
    //         }

    //         // Create PAYMENT record
    //         $payment = Payment::create([
    //             'user_id' => $userId,
    //             'license_id' => $license->id,
    //             'transaction_id' => $session->payment_intent ?? null, // optional, model auto-generates if null
    //             'amount' => ($session->amount_total ?? 0) / 100,
    //             'tax_amount' => 0,
    //             'discount_amount' => 0,
    //             'currency' => strtoupper($session->currency ?? $plan->currency ?? 'USD'),
    //             'payment_method' => 'card',
    //             'payment_gateway' => 'stripe',
    //             'gateway_reference_id' => $session->id,
    //             'status' => 'completed',
    //             'payment_details' => [
    //                 'stripe_session_id' => $session->id,
    //                 'stripe_payment_intent' => $session->payment_intent ?? null,
    //                 'stripe_customer' => $session->customer ?? null,
    //                 'payment_status' => $session->payment_status ?? null,
    //                 'checkout_status' => $session->status ?? null,
    //             ],
    //             'paid_at' => now(),
    //             'failure_reason' => null,
    //             'billing_name' => $session->customer_details->name ?? null,
    //             'billing_email' => $session->customer_details->email ?? null,
    //             'billing_phone' => $session->customer_details->phone ?? null,
    //             'billing_address' => $session->customer_details->address->line1 ?? null,
    //             'billing_city' => $session->customer_details->address->city ?? null,
    //             'billing_state' => $session->customer_details->address->state ?? null,
    //             'billing_country' => $session->customer_details->address->country ?? null,
    //             'billing_zip' => $session->customer_details->address->postal_code ?? null,
    //             'metadata' => [
    //                 'plan_id' => $plan->id,
    //                 'tool_id' => $plan->tool_id,
    //                 'package_name' => $session->metadata->package_name ?? $plan->name,
    //                 'billing_cycle' => $plan->billing_cycle,
    //             ],
    //         ]);

    //         return redirect()->route('plans.show', $plan->id)
    //             ->with('success', 'Payment successful! License created: ' . $license->license_key . ' | Invoice: ' . $payment->invoice_number);

    //     } catch (\Exception $e) {
    //         Log::error('Stripe success error: ' . $e->getMessage(), [
    //             'plan_id' => $plan->id,
    //             'session_id' => $sessionId,
    //         ]);

    //         return redirect()->route('plans.show', $plan->id)
    //             ->with('error', 'Payment verified, but database insert failed. Check logs.');
    //     }
    // }

    public function stripeSuccess(Request $request, Plan $plan)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('plans.show', $plan->id)
                ->with('error', 'Stripe session ID missing.');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = Session::retrieve($sessionId);

            // Make sure payment is completed
            if ($session->payment_status !== 'paid') {
                return redirect()->route('plans.show', $plan->id)
                    ->with('error', 'Payment not completed.');
            }

            // Prevent duplicate PAYMENT creation if page refreshes
            $existingPayment = Payment::where('gateway_reference_id', $session->id)->first();

            if ($existingPayment) {
                // If payment exists, check if license exists and redirect to subscriptions
                $license = License::where('metadata->stripe_session_id', $session->id)->first();
                if ($license) {
                    return redirect()->route('subscriptions.index')
                        ->with('success', 'Payment already processed! Your license key: ' . $license->license_key);
                }

                return redirect()->route('subscriptions.index')
                    ->with('success', 'Payment already processed. Check your subscriptions.');
            }

            // Get user from metadata (or fallback to current logged-in user)
            $userId = $session->metadata->user_id ?? Auth::id();

            if (!$userId || $userId === 'guest') {
                return redirect()->route('plans.show', $plan->id)
                    ->with('error', 'Unable to identify user for payment/license creation.');
            }

            $startsAt = now();
            $expiresAt = null;

            // Calculate expiry based on billing cycle or duration_days
            if (!empty($plan->duration_days)) {
                $expiresAt = now()->addDays($plan->duration_days);
            } else {
                switch ($plan->billing_cycle) {
                    case 'monthly':
                        $expiresAt = now()->addMonth();
                        break;
                    case 'quarterly':
                        $expiresAt = now()->addMonths(3);
                        break;
                    case 'yearly':
                        $expiresAt = now()->addYear();
                        break;
                    case 'lifetime':
                    case 'one_time':
                    default:
                        $expiresAt = null;
                        break;
                }
            }

            // Prevent duplicate LICENSE creation
            $existingLicense = License::where('metadata->stripe_session_id', $session->id)->first();

            if (!$existingLicense) {
                // Generate a more readable license key
                $licenseKey = strtoupper(
                    substr($plan->tool?->name ?? 'TOOL', 0, 3) . '-' .
                    substr($plan->name, 0, 3) . '-' .
                    strtoupper(Str::random(8)) . '-' .
                    str_pad($userId, 4, '0', STR_PAD_LEFT)
                );

                $license = License::create([
                    'user_id' => $userId,
                    'tool_id' => $plan->tool_id,
                    'plan_id' => $plan->id,
                    'license_key' => $licenseKey,
                    'package_name' => $session->metadata->package_name ?? $plan->name,
                    'bundle_id' => null,
                    'domain' => null,
                    'environment' => 'production',
                    'allowed_domains' => [],
                    'allowed_ips' => [],
                    'device_ids' => [],
                    'device_count' => 0,
                    'api_calls_used' => 0,
                    'api_calls_limit' => $plan->api_call_limit,
                    'starts_at' => $startsAt,
                    'expires_at' => $expiresAt,
                    'last_verified_at' => null,
                    'last_used_at' => null,
                    'status' => 'active',
                    'metadata' => [
                        'payment_method' => 'stripe',
                        'stripe_session_id' => $session->id,
                        'stripe_payment_intent' => $session->payment_intent ?? null,
                        'stripe_customer' => $session->customer ?? null,
                        'amount_total' => $session->amount_total ?? null,
                        'currency' => strtoupper($session->currency ?? $plan->currency ?? 'USD'),
                    ],
                    'is_trial' => false,
                    'auto_renew' => true,
                    'notes' => 'Created automatically after successful Stripe payment.',
                ]);
            } else {
                $license = $existingLicense;
            }

            // Create PAYMENT record
            $payment = Payment::create([
                'user_id' => $userId,
                'license_id' => $license->id,
                'transaction_id' => $session->payment_intent ?? 'TXN_' . strtoupper(Str::random(12)),
                'amount' => ($session->amount_total ?? 0) / 100,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'currency' => strtoupper($session->currency ?? $plan->currency ?? 'USD'),
                'payment_method' => 'card',
                'payment_gateway' => 'stripe',
                'gateway_reference_id' => $session->id,
                'status' => 'completed',
                'payment_details' => [
                    'stripe_session_id' => $session->id,
                    'stripe_payment_intent' => $session->payment_intent ?? null,
                    'stripe_customer' => $session->customer ?? null,
                    'payment_status' => $session->payment_status ?? null,
                    'checkout_status' => $session->status ?? null,
                ],
                'paid_at' => now(),
                'failure_reason' => null,
                'billing_name' => $session->customer_details->name ?? null,
                'billing_email' => $session->customer_details->email ?? null,
                'billing_phone' => $session->customer_details->phone ?? null,
                'billing_address' => $session->customer_details->address->line1 ?? null,
                'billing_city' => $session->customer_details->address->city ?? null,
                'billing_state' => $session->customer_details->address->state ?? null,
                'billing_country' => $session->customer_details->address->country ?? null,
                'billing_zip' => $session->customer_details->address->postal_code ?? null,
                'metadata' => [
                    'plan_id' => $plan->id,
                    'tool_id' => $plan->tool_id,
                    'package_name' => $session->metadata->package_name ?? $plan->name,
                    'billing_cycle' => $plan->billing_cycle,
                ],
            ]);

            // Redirect to subscriptions page with success message
            return redirect()->route('subscriptions.index')
                ->with('success', 'Payment successful! Your license key: ' . $license->license_key . ' has been created.');

        } catch (\Exception $e) {
            Log::error('Stripe success error: ' . $e->getMessage(), [
                'plan_id' => $plan->id,
                'session_id' => $sessionId,
            ]);

            return redirect()->route('plans.show', $plan->id)
                ->with('error', 'Payment verified, but license creation failed. Please contact support.');
        }
    }

    public function stripeCancel(Plan $plan)
    {
        return redirect()->route('plans.show', $plan->id)
            ->with('error', 'Payment was cancelled. No charges were made.');
    }

}
