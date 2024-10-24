<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $timestamps = false;
    protected $fillable = [
        'name', 'sku', 'status', 'variations', 'price', 'currency'
    ];
}
