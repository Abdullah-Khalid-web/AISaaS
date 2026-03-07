<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory; // Removed SoftDeletes

    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'license_id',
        'transaction_id',
        'invoice_number',
        'amount',
        'tax_amount',
        'discount_amount',
        'currency',
        'payment_method',
        'payment_gateway',
        'gateway_reference_id',
        'status',
        'payment_details',
        'refund_details',
        'paid_at',
        'refunded_at',
        'failure_reason',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_zip',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payment_details' => 'array',
        'refund_details' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
        // Removed deleted_at cast
    ];

    protected $attributes = [
        'status' => 'pending',
        'currency' => 'USD'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('paid_at', [$start, $end]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    // Accessors
    public function getSubtotalAttribute()
    {
        return $this->amount - $this->tax_amount + $this->discount_amount;
    }

    public function getTotalAttribute()
    {
        return $this->amount;
    }

    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    public function getFormattedTaxAttribute()
    {
        return $this->currency . ' ' . number_format($this->tax_amount, 2);
    }

    public function getFormattedDiscountAttribute()
    {
        return $this->currency . ' ' . number_format($this->discount_amount, 2);
    }

    public function getIsSuccessfulAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsRefundedAttribute()
    {
        return in_array($this->status, ['refunded', 'partially_refunded']);
    }

    public function getRefundedAmountAttribute()
    {
        return $this->refund_details['amount'] ?? 0;
    }

    public function getBillingFullAddressAttribute()
    {
        $parts = array_filter([
            $this->billing_address,
            $this->billing_city,
            $this->billing_state,
            $this->billing_zip,
            $this->billing_country
        ]);

        return implode(', ', $parts);
    }

    // Helper Methods
    public function markAsCompleted($paidAt = null)
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => $paidAt ?? now()
        ]);
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason
        ]);
    }

    public function refund($amount = null, $reason = null)
    {
        $refundAmount = $amount ?? $this->amount;

        $this->update([
            'status' => $refundAmount >= $this->amount ? 'refunded' : 'partially_refunded',
            'refunded_at' => now(),
            'refund_details' => [
                'amount' => $refundAmount,
                'reason' => $reason,
                'refunded_by' => auth()->id(),
                'refunded_at' => now()->toDateTimeString()
            ]
        ]);
    }

    public function generateInvoiceNumber()
    {
        if (!$this->invoice_number) {
            $this->update([
                'invoice_number' => 'INV-' . str_pad($this->id, 8, '0', STR_PAD_LEFT)
            ]);
        }

        return $this->invoice_number;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = strtoupper(uniqid('TXN_'));
            }
        });

        static::created(function ($payment) {
            $payment->generateInvoiceNumber();
        });
    }

    // Helper methods
    public function isCompleted()
    {
        return $this->status === 'completed';
    }
}
