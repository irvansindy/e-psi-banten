<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupSim extends Model
{
    protected $fillable = [
        'name',
    ];

    public function psychologyTests()
    {
        return $this->hasMany(PsychologyTest::class, 'group_sim_id');
    }
}