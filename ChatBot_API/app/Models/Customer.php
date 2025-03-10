<?php

namespace App\Models;

use Mongodb\Laravel\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'mongodb';
    protected $fillable = ['phone_number', 'name', 'assigned_agent_id','required_skill','language',];

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
