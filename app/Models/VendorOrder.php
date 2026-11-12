<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class VendorOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'order_id',
        'subtotal',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
