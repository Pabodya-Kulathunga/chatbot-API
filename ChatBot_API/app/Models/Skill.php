<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'language', 'description'];
    protected $primaryKey = 'id';

    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'agent_skills', 'skill_id', 'emp_id');
    }

    public function agentSkills()
    {
        return $this->hasMany(AgentSkill::class, 'skill_id');
    }
}

