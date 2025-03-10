<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    

    protected $fillable = [
        'emp_id', 'name', 'email', 'password', 'role', // Other fields...
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Override the default authentication identifier name.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'emp_id'; // Use 'emp_id' as the identifier
    }

    //
    public function agentSkills()
    {
        return $this->hasMany(AgentSkill::class, 'emp_id', 'emp_id');
    }
}
