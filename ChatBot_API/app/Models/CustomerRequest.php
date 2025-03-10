<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;

class CustomerRequest extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'customer_requests';

    protected $fillable = [
        'customer_phone',
        'required_skill',
        'language',
    ];
}
