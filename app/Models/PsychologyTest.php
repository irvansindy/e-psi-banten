<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychologyTest extends Model
{
    protected $fillable = [
        'name',
        'gender',
        'place_of_birth',
        'date_of_birth',
        'age',
        'sim_id',
        'group_sim_id',
        'domicile',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'age' => 'integer',
        'sim_id' => 'integer',
        'group_sim_id' => 'integer',
        'domicile' => 'integer',
    ];
}