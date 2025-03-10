<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['user_id', 'contact_phone_number'];
}
