<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentSkill extends Model
{

    use HasFactory;
    protected $table = 'agent_skill';
    protected $fillable = ['skill_id', 'emp_id'];

    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'emp_id', 'emp_id');

    }



}
