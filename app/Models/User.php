<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password','is_admin'
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // Products this user is tracking
    // Products this user is tracking personally
    public function trackedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'user_products')
            ->using(UserProduct::class)
            ->withPivot([
                'store_id', 'custom_name', 'purchase_unit', 
                'pieces_per_bulk', 'price', 'is_tracked', 'user_notes'
            ])
            ->withTimestamps();
    }

    // Products this user originally submitted to the global catalog
    public function createdProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }
}
