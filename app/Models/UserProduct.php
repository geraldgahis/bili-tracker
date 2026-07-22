<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProduct extends Pivot
{
    protected $table = 'user_products';

    // Accessor: Calculate exact cost per piece for bulk items
    public function getUnitPriceAttribute(): float
    {
        $pieces = $this->pieces_per_bulk > 0 ? $this->pieces_per_bulk : 1;
        return round($this->price / $pieces, 2);
    }

    // Accessor: Show custom name if user set one, otherwise fallback to global product name
    public function getDisplayNameAttribute(): string
    {
        if ($this->custom_name) {
            return $this->custom_name;
        }

        return $this->product->name ?? 'Unknown Product';
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}