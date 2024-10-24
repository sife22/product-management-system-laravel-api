<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id','name', 'sku', 'status', 'variations', 'price', 'currency'
    ];
}
