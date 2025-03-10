<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    // Specify the table if it's not the default "agents"
    protected $table = 'agent';

    // Primary key
    protected $primaryKey = 'emp_id';

    // Define fillable attributes (fields that are mass assignable)
    protected $fillable = ['emp_id', 'group_code'];

    // Relationship to AgentSkill model
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'agent_skills', 'emp_id', 'skill_id');
    }

    // Relationship to Group model (assuming group is another table with group_code as primary)
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_code', 'group_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'emp_id', 'emp_id'); // Adjust foreign key if needed
    }

}
