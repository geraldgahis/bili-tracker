<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = ['name', 'location'];

    // If you want to see all price histories tied to this store
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }
}
