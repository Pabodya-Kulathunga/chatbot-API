<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'group';  // Ensure this matches the actual table name
    protected $primaryKey = 'id'; // Primary key column
    protected $fillable = ['group_name', 'group_code', 'address', 'contact_number']; // Ensure 'group_code' is fillable


}
