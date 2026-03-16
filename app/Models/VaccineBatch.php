<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaccineBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_code',
        'received_date',
        'expiry_date',
        'quantity_received',
        'quantity_remaining',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'expiry_date' => 'date',
            'quantity_received' => 'decimal:2',
            'quantity_remaining' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function invoiceItemVaccineBatches(): HasMany
    {
        return $this->hasMany(InvoiceItemVaccineBatch::class);
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query
            ->whereDate('expiry_date', '>=', today()->toDateString())
            ->where('quantity_remaining', '>', 0);
    }

    public function scopeFefo(Builder $query): Builder
    {
        return $query
            ->orderBy('expiry_date')
            ->orderBy('id');
    }
}