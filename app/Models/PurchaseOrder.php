<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    public const DRAFT = 'draft';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const SENT = 'sent';
    public const PARTIAL = 'partial';
    public const RECEIVED = 'received';
    public const CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::DRAFT => 'Draft',
        self::PENDING => 'Pending approval',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected',
        self::SENT => 'Sent to supplier',
        self::PARTIAL => 'Partially received',
        self::RECEIVED => 'Received',
        self::CANCELLED => 'Cancelled',
    ];

    protected const STATUS_BADGES = [
        self::DRAFT => 'text-bg-secondary',
        self::PENDING => 'text-bg-warning',
        self::APPROVED => 'text-bg-info',
        self::REJECTED => 'text-bg-danger',
        self::SENT => 'text-bg-primary',
        self::PARTIAL => 'text-bg-primary',
        self::RECEIVED => 'text-bg-success',
        self::CANCELLED => 'text-bg-dark',
    ];

    /**
     * Optional note stored with the next status change in the approval history.
     * Declared as a real property so Eloquent does not treat it as a column.
     */
    public $statusComment;

    protected $fillable = [
        'reference_number', 'supplier_id', 'status', 'order_date', 'expected_delivery_date', 'delivered_at',
        'subtotal', 'tax_amount', 'total', 'notes', 'terms', 'auto_generated',
        'created_by', 'approved_by', 'approved_at', 'sent_at', 'rejected_at', 'rejection_reason',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivered_at' => 'datetime',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'rejected_at' => 'datetime',
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'total' => 'float',
        'auto_generated' => 'boolean',
    ];

    /* ---------------- Relationships ---------------- */

    public function supplier()
    {
        // withTrashed: a soft-deleted supplier must still show on its historical orders.
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->oldest();
    }

    /* ---------------- Query scopes ---------------- */

    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }

    /** Orders that are still in progress: not received, cancelled or rejected. */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::RECEIVED, self::CANCELLED, self::REJECTED]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Supported filters: search (reference), status, supplier_id, date_from, date_to.
     */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $query->where('reference_number', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['status']) && array_key_exists($filters['status'], self::STATUS_LABELS)) {
            $query->byStatus($filters['status']);
        }
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        return $query;
    }

    /* ---------------- Helper methods ---------------- */

    /** Recalculate subtotal, tax and total from the line items and save. */
    public function calculateTotal(): self
    {
        $subtotal = round($this->items()->get()->sum(function ($item) {
            return $item->line_total;
        }), 2);
        $tax = round($subtotal * (float) config('procurement.tax_rate'), 2);

        $this->forceFill(['subtotal' => $subtotal, 'tax_amount' => $tax, 'total' => round($subtotal + $tax, 2)])->save();

        return $this;
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::DRAFT;
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === self::DRAFT && $this->items()->exists();
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::PENDING;
    }

    public function canBeSent(): bool
    {
        return $this->status === self::APPROVED;
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, [self::SENT, self::PARTIAL], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::DRAFT, self::PENDING, self::APPROVED, self::SENT], true);
    }

    /** Multi-level approval: big orders must be approved by an administrator. */
    public function requiresAdminApproval(): bool
    {
        return $this->total >= (float) config('procurement.admin_approval_threshold');
    }

    public function isFullyReceived(): bool
    {
        return $this->items->isNotEmpty() && $this->items->every(function ($item) {
            return $item->received_quantity >= $item->quantity;
        });
    }

    public function isOverdue(): bool
    {
        return $this->expected_delivery_date
            && in_array($this->status, [self::SENT, self::PARTIAL], true)
            && $this->expected_delivery_date->lt(today());
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeAttribute()
    {
        return self::STATUS_BADGES[$this->status] ?? 'text-bg-secondary';
    }

    /** Creates a unique reference such as PO-20261106-0001. */
    public static function generateReferenceNumber(?Carbon $date = null): string
    {
        $prefix = 'PO-' . ($date ?: now())->format('Ymd') . '-';
        $count = static::withTrashed()->where('reference_number', 'like', $prefix . '%')->count();

        do {
            $count++;
            $reference = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
        } while (static::withTrashed()->where('reference_number', $reference)->exists());

        return $reference;
    }
}
