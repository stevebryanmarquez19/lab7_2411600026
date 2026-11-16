<?php

namespace App\Models;

use App\Services\SupplierPerformanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    public const PAYMENT_TERMS = ['COD', 'Prepaid', 'Net 15', 'Net 30', 'Net 45', 'Net 60'];

    protected $fillable = [
        'company_name', 'contact_person', 'email', 'phone', 'address',
        'payment_terms', 'lead_time_days', 'minimum_order_amount', 'is_active', 'notes',
    ];

    protected $casts = [
        'lead_time_days' => 'integer',
        'minimum_order_amount' => 'float',
        'is_active' => 'boolean',
    ];

    /* ---------------- Relationships ---------------- */

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest();
    }

    /* ---------------- Query scopes ---------------- */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithLeadTime($query, $days)
    {
        return $query->where('lead_time_days', '<=', (int) $days);
    }

    /**
     * Supported filters: search, status (active|inactive|deleted), max_lead_time, sort, direction.
     */
    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('company_name', 'like', $term)
                  ->orWhere('contact_person', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        $status = $filters['status'] ?? '';
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'deleted') {
            $query->onlyTrashed();
        }

        if (isset($filters['max_lead_time']) && $filters['max_lead_time'] !== '') {
            $query->withLeadTime($filters['max_lead_time']);
        }

        $sortable = ['company_name', 'contact_person', 'lead_time_days', 'minimum_order_amount', 'created_at'];
        $sort = in_array($filters['sort'] ?? '', $sortable, true) ? $filters['sort'] : 'company_name';
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $direction);
    }

    /* ---------------- Accessors / helpers ---------------- */

    public function getFullContactAttribute()
    {
        return sprintf('%s · %s · %s', $this->contact_person, $this->email, $this->phone);
    }

    /**
     * On-time delivery percentage (0-100) or null when nothing has been delivered yet.
     * Uses the cached SupplierPerformanceService so the number matches the report.
     */
    public function getPerformanceRatingAttribute()
    {
        return app(SupplierPerformanceService::class)->calculatePerformance($this)['on_time_rate'];
    }
}
