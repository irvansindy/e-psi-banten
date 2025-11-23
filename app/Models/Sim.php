<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sim extends Model
{
    protected $fillable = [
        'name',
    ];

    public function psychologyTests()
    {
        return $this->hasMany(PsychologyTest::class, 'sim_id');
    }
}