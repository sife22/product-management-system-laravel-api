<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'color', 'size', 'quantity', 'is_available', 'product_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
