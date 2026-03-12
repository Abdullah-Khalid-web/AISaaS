<?php
// app/Http/Controllers/AI/SubscriptionController.php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AiTool;
use App\Models\Plan;
use App\Models\License;
use App\Models\Payment;
use App\Models\User;
use App\Services\LicenseKeyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseKeyService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Display a listing of subscriptions (licenses)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['admin', 'super-admin']);

        // Base query - without eager loading payments to avoid soft delete issues
        $query = License::with(['user', 'tool', 'plan']);

        // If not admin, only show user's own licenses
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('license_key', 'like', "%{$search}%")
                  ->orWhere('package_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tool', function ($toolQuery) use ($search) {
                      $toolQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tool_id')) {
            $query->where('tool_id', $request->tool_id);
        }

        // Get licenses with pagination
        $licenses = $query->orderBy('created_at', 'desc')
                         ->paginate(15)
                         ->through(function ($license) {
                             // Get payments separately
                             $payments = $license->payments()
                                 ->latest()
                                 ->limit(1)
                                 ->get();

                             $lastPayment = $payments->first();

                             return [
                                 'id' => $license->id,
                                 'license_key' => $license->license_key,
                                 'package_name' => $license->package_name,
                                 'status' => $license->status,
                                 'expires_at' => $license->expires_at,
                                 'api_calls_used' => $license->api_calls_used,
                                 'api_calls_limit' => $license->api_calls_limit,
                                 'device_limit' => $license->device_limit,
                                 'created_at' => $license->created_at,
                                 'user' => $license->user ? [
                                     'id' => $license->user->id,
                                     'name' => $license->user->name,
                                     'email' => $license->user->email
                                 ] : null,
                                 'tool' => $license->tool ? [
                                     'id' => $license->tool->id,
                                     'name' => $license->tool->name,
                                     'metadata' => $license->tool->metadata
                                 ] : null,
                                 'plan' => $license->plan ? [
                                     'id' => $license->plan->id,
                                     'name' => $license->plan->name,
                                     'price' => $license->plan->price,
                                     'currency' => $license->plan->currency,
                                     'billing_cycle' => $license->plan->billing_cycle
                                 ] : null,
                                 'last_payment' => $lastPayment ? [
                                     'amount' => $lastPayment->amount,
                                     'currency' => $lastPayment->currency,
                                     'paid_at' => $lastPayment->paid_at
                                 ] : null
                             ];
                         });

        // Get tools for filter (admin only)
        $tools = [];
        if ($isAdmin) {
            $tools = AiTool::select('id', 'name')
                ->where('is_active', true)
                ->get();
        }

        // Calculate stats for admin
        $stats = [];
        if ($isAdmin) {
            $stats = [
                'total' => License::count(),
                'active' => License::where('status', 'active')->count(),
                'expired' => License::where('status', 'expired')->count(),
                'revenue' => Payment::where('status', 'completed')->sum('amount') ?? 0
            ];
        }

        return Inertia::render('Subscriptions/Index', [
            'licenses' => $licenses,
            'filters' => $request->only(['search', 'status', 'tool_id']),
            'tools' => $tools,
            'stats' => $stats
        ]);
    }

    // Add this method to show the subscription form
    public function show(AiTool $tool)
    {
        // Check if user already has active license
        $existingLicense = License::where('user_id', Auth::id())
            ->where('tool_id', $tool->id)
            ->where('status', 'active')
            ->first();

        if ($existingLicense) {
            return redirect()->route('licenses.show', $existingLicense)
                ->with('error', 'You already have an active license for this tool.');
        }

        $tool->load(['plans' => function($query) {
            $query->where('is_active', true)->orderBy('price');
        }]);

        return Inertia::render('Subscriptions/Subscribe', [
            'tool' => [
                'id' => $tool->id,
                'name' => $tool->name,
                'description' => $tool->description,
                'metadata' => $tool->metadata ?? [],
                'plans' => $tool->plans->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'currency' => $plan->currency,
                        'billing_cycle' => $plan->billing_cycle,
                        'is_popular' => $plan->is_popular,
                        'description' => $plan->description,
                        'features' => $plan->features ?? [],
                        'api_call_limit' => $plan->api_call_limit,
                        'device_limit' => $plan->device_limit,
                    ];
                })
            ]
        ]);
    }

    // Update the store method to return license ID
    public function store(Request $request, AiTool $tool)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'package_name' => 'nullable|string|max:255',
            'payment_method' => 'required|string|in:stripe,paypal'
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        // Verify plan belongs to tool
        if ($plan->tool_id !== $tool->id) {
            return back()->withErrors([
                'plan_id' => 'Invalid plan selected for this tool.'
            ]);
        }

        // Check if user already has active license for this tool
        $existingLicense = License::where('user_id', Auth::id())
            ->where('tool_id', $tool->id)
            ->where('status', 'active')
            ->first();

        if ($existingLicense) {
            return back()->withErrors([
                'error' => 'You already have an active license for this tool'
            ]);
        }

        DB::beginTransaction();

        try {
            // Calculate expiry based on plan duration
            $expiresAt = null;
            if ($plan->duration_days) {
                $expiresAt = Carbon::now()->addDays($plan->duration_days);
            } elseif ($plan->billing_cycle === 'monthly') {
                $expiresAt = Carbon::now()->addMonth();
            } elseif ($plan->billing_cycle === 'quarterly') {
                $expiresAt = Carbon::now()->addMonths(3);
            } elseif ($plan->billing_cycle === 'yearly') {
                $expiresAt = Carbon::now()->addYear();
            }

            // Generate license key
            $licenseKey = 'LIC-' . strtoupper(uniqid()) . '-' . str_pad(Auth::id(), 4, '0', STR_PAD_LEFT);

            // Create license
            $license = License::create([
                'user_id' => Auth::id(),
                'tool_id' => $tool->id,
                'plan_id' => $plan->id,
                'license_key' => $licenseKey,
                'package_name' => $request->package_name ?? $plan->name,
                'expires_at' => $expiresAt,
                'status' => 'active',
                'api_calls_limit' => $plan->api_call_limit,
                'api_calls_used' => 0,
                'device_limit' => $plan->device_limit,
                'metadata' => [
                    'payment_method' => $request->payment_method,
                    'subscribed_at' => now()->toDateTimeString(),
                    'billing_cycle' => $plan->billing_cycle
                ]
            ]);

            // Create payment record
            $license->payments()->create([
                'user_id' => Auth::id(),
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'payment_method' => $request->payment_method,
                'payment_gateway' => $request->payment_method,
                'transaction_id' => 'TXN_' . strtoupper(uniqid()),
                'status' => 'completed',
                'paid_at' => now(),
                'metadata' => [
                    'plan_name' => $plan->name,
                    'billing_cycle' => $plan->billing_cycle
                ]
            ]);

            DB::commit();

            // Redirect to the license view page
            return redirect()->route('licenses.show', $license)
                ->with('success', 'Successfully subscribed to ' . $tool->name . '! Your license key has been generated.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Subscription failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'tool_id' => $tool->id,
                'plan_id' => $request->plan_id
            ]);

            return back()->withErrors([
                'error' => 'Failed to process subscription: ' . $e->getMessage()
            ]);
        }
    }

    // Add showLicense method (if not already present)
    public function showLicense(License $license)
    {
        // Check authorization
        if (Auth::id() !== $license->user_id && !Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $license->load(['user', 'tool', 'plan', 'payments' => function($query) {
            $query->latest();
        }]);

        return Inertia::render('Licenses/Show', [
            'license' => [
                'id' => $license->id,
                'license_key' => $license->license_key,
                'package_name' => $license->package_name,
                'status' => $license->status,
                'expires_at' => $license->expires_at,
                'api_calls_used' => $license->api_calls_used,
                'api_calls_limit' => $license->api_calls_limit,
                'device_count' => $license->device_count,
                'device_limit' => $license->device_limit,
                'device_ids' => $license->device_ids,
                'allowed_domains' => $license->allowed_domains,
                'auto_renew' => $license->auto_renew,
                'is_trial' => $license->is_trial,
                'created_at' => $license->created_at,
                'metadata' => $license->metadata,
                'isExpired' => $license->isExpired(),
                'user' => $license->user ? [
                    'id' => $license->user->id,
                    'name' => $license->user->name,
                    'email' => $license->user->email
                ] : null,
                'tool' => $license->tool ? [
                    'id' => $license->tool->id,
                    'name' => $license->tool->name,
                    'version' => $license->tool->version,
                    'metadata' => $license->tool->metadata
                ] : null,
                'plan' => $license->plan ? [
                    'id' => $license->plan->id,
                    'name' => $license->plan->name,
                    'price' => $license->plan->price,
                    'currency' => $license->plan->currency,
                    'billing_cycle' => $license->plan->billing_cycle,
                    'features' => $license->plan->features
                ] : null,
                'payments' => $license->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'transaction_id' => $payment->transaction_id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'payment_method' => $payment->payment_method,
                        'status' => $payment->status,
                        'paid_at' => $payment->paid_at
                    ];
                })
            ],
            'isAdmin' => Auth::user()->hasAnyRole(['admin', 'super-admin'])
        ]);
    }

    /**
     * Show renewal form for a license
     */
    public function renew(License $license)
    {
        // Check authorization
        if (Auth::id() !== $license->user_id && !Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        // Only active or expired licenses can be renewed
        if (!in_array($license->status, ['active', 'expired'])) {
            return redirect()->route('licenses.show', $license)
                ->with('error', 'This license cannot be renewed.');
        }

        $license->load(['tool', 'plan']);

        return Inertia::render('Subscriptions/Renew', [
            'license' => [
                'id' => $license->id,
                'license_key' => $license->license_key,
                'expires_at' => $license->expires_at,
                'status' => $license->status,
                'tool' => [
                    'id' => $license->tool->id,
                    'name' => $license->tool->name,
                    'metadata' => $license->tool->metadata
                ],
                'plan' => [
                    'id' => $license->plan->id,
                    'name' => $license->plan->name,
                    'price' => $license->plan->price,
                    'currency' => $license->plan->currency,
                    'billing_cycle' => $license->plan->billing_cycle
                ]
            ]
        ]);
    }

    /**
     * Process license renewal
     */
    public function processRenewal(Request $request, License $license)
    {
        // Check authorization
        if (Auth::id() !== $license->user_id && !Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $request->validate([
            'payment_method' => 'required|string|in:stripe,paypal'
        ]);

        DB::beginTransaction();

        try {
            $plan = $license->plan;

            // Calculate new expiry date
            $newExpiresAt = null;
            if ($plan->duration_days) {
                $newExpiresAt = Carbon::now()->addDays($plan->duration_days);
            } elseif ($plan->billing_cycle === 'monthly') {
                $newExpiresAt = Carbon::now()->addMonth();
            } elseif ($plan->billing_cycle === 'quarterly') {
                $newExpiresAt = Carbon::now()->addMonths(3);
            } elseif ($plan->billing_cycle === 'yearly') {
                $newExpiresAt = Carbon::now()->addYear();
            }

            // Update license
            $license->update([
                'status' => 'active',
                'expires_at' => $newExpiresAt,
                'api_calls_used' => 0, // Reset usage for new period
                'metadata' => array_merge($license->metadata ?? [], [
                    'last_renewed_at' => now()->toDateTimeString(),
                    'renewal_count' => ($license->metadata['renewal_count'] ?? 0) + 1
                ])
            ]);

            // Create payment record for renewal
            $license->payments()->create([
                'user_id' => $license->user_id,
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'payment_method' => $request->payment_method,
                'payment_gateway' => $request->payment_method,
                'transaction_id' => strtoupper($request->payment_method) . '_RENEWAL_' . uniqid(),
                'status' => 'completed',
                'paid_at' => now(),
                'metadata' => [
                    'plan_name' => $plan->name,
                    'billing_cycle' => $plan->billing_cycle,
                    'renewal' => true
                ]
            ]);

            DB::commit();

            return redirect()->route('licenses.show', $license)
                ->with('success', 'License renewed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Renewal failed: ' . $e->getMessage(), [
                'license_id' => $license->id,
                'user_id' => Auth::id()
            ]);

            return back()->withErrors([
                'error' => 'Failed to process renewal: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export subscriptions data (admin only)
     */
    public function export(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $query = License::with(['user', 'tool', 'plan']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('license_key', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tool_id')) {
            $query->where('tool_id', $request->tool_id);
        }

        $licenses = $query->get();

        // Generate CSV
        $filename = 'subscriptions_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($licenses) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'License ID',
                'License Key',
                'User Name',
                'User Email',
                'Tool',
                'Plan',
                'Status',
                'Created At',
                'Expires At',
                'API Calls Used',
                'API Calls Limit'
            ]);

            // Data
            foreach ($licenses as $license) {
                fputcsv($file, [
                    $license->id,
                    $license->license_key,
                    $license->user->name ?? 'N/A',
                    $license->user->email ?? 'N/A',
                    $license->tool->name ?? 'N/A',
                    $license->plan->name ?? 'N/A',
                    $license->status,
                    $license->created_at->format('Y-m-d H:i:s'),
                    $license->expires_at ? $license->expires_at->format('Y-m-d H:i:s') : 'Never',
                    $license->api_calls_used ?? 0,
                    $license->api_calls_limit ?? 'Unlimited'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get subscription statistics (admin only)
     */
    public function statistics()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        $stats = [
            'overview' => [
                'total_subscriptions' => License::count(),
                'active_subscriptions' => License::where('status', 'active')->count(),
                'expired_subscriptions' => License::where('status', 'expired')->count(),
                'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
                'average_order_value' => Payment::where('status', 'completed')->avg('amount') ?? 0
            ],
            'by_tool' => AiTool::withCount(['licenses' => function ($query) {
                    $query->where('status', 'active');
                }])
                ->get()
                ->map(function ($tool) {
                    // Calculate revenue for this tool
                    $revenue = Payment::where('status', 'completed')
                        ->whereIn('license_id', $tool->licenses()->pluck('id'))
                        ->sum('amount');

                    return [
                        'tool_name' => $tool->name,
                        'active_subscriptions' => $tool->licenses_count,
                        'revenue' => $revenue ?? 0
                    ];
                }),
            'by_plan' => Plan::withCount(['licenses' => function ($query) {
                    $query->where('status', 'active');
                }])
                ->get()
                ->map(function ($plan) {
                    return [
                        'plan_name' => $plan->name,
                        'tool_name' => $plan->tool->name ?? 'N/A',
                        'active_subscriptions' => $plan->licenses_count,
                        'price' => $plan->price,
                        'currency' => $plan->currency
                    ];
                }),
            'recent_activity' => License::with(['user', 'tool'])
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($license) {
                    return [
                        'user_name' => $license->user->name ?? 'N/A',
                        'tool_name' => $license->tool->name ?? 'N/A',
                        'status' => $license->status,
                        'created_at' => $license->created_at->diffForHumans()
                    ];
                })
        ];

        return Inertia::render('Subscriptions/Statistics', [
            'stats' => $stats
        ]);
    }

    /**
     * Cancel a subscription (soft delete/revoke)
     */
    public function cancel(License $license)
    {
        // Check authorization
        if (Auth::id() !== $license->user_id && !Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }

        if ($license->status !== 'active') {
            return back()->withErrors([
                'error' => 'Only active subscriptions can be cancelled.'
            ]);
        }

        DB::transaction(function () use ($license) {
            $license->update([
                'status' => 'cancelled',
                'metadata' => array_merge($license->metadata ?? [], [
                    'cancelled_at' => now()->toDateTimeString(),
                    'cancelled_by' => Auth::id()
                ])
            ]);
        });

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription cancelled successfully.');
    }

    public function subscribe(AiTool $tool)
    {
        // Check if user already has active license
        $existingLicense = License::where('user_id', Auth::id())
            ->where('tool_id', $tool->id)
            ->where('status', 'active')
            ->first();

        if ($existingLicense) {
            return redirect()->route('licenses.show', $existingLicense)
                ->with('error', 'You already have an active license for this tool.');
        }

        $tool->load(['plans' => function($query) {
            $query->where('is_active', true)->orderBy('price');
        }]);

        return Inertia::render('Subscriptions/Subscribe', [
            'tool' => [
                'id' => $tool->id,
                'name' => $tool->name,
                'description' => $tool->description,
                'metadata' => $tool->metadata ?? [],
                'plans' => $tool->plans->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'currency' => $plan->currency,
                        'billing_cycle' => $plan->billing_cycle,
                        'is_popular' => $plan->is_popular,
                        'description' => $plan->description,
                        'features' => $plan->features ?? [],
                        'api_call_limit' => $plan->api_call_limit,
                        'device_limit' => $plan->device_limit,
                    ];
                })
            ]
        ]);
    }

}
