<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'description',
        'photo',
        'initial_stock',
    ];

    protected $appends = ['current_stock'];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the transactions for the product.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get current stock: initial_stock + masuk - keluar
     */
    public function getCurrentStockAttribute(): int
    {
        $masuk = $this->transactions()->where('type', 'masuk')->sum('quantity');
        $keluar = $this->transactions()->where('type', 'keluar')->sum('quantity');

        return $this->initial_stock + $masuk - $keluar;
    }
}
