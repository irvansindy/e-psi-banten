<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'photo', // Tambah ini
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'age' => 'integer',
        'sim_id' => 'integer',
        'group_sim_id' => 'integer',
        'domicile' => 'string',
    ];

    protected $appends = ['photo_url']; // Tambah ini

    public function sim()
    {
        return $this->belongsTo(Sim::class, 'sim_id');
    }

    public function groupSim()
    {
        return $this->belongsTo(GroupSim::class, 'group_sim_id');
    }

    // Accessor untuk URL foto
    public function getPhotoUrlAttribute()
    {
        if ($this->photo && Storage::disk('public')->exists($this->photo)) {
            return Storage::url($this->photo);
        }
        return null;
    }

    // Event untuk hapus foto saat data dihapus
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if ($model->photo && Storage::disk('public')->exists($model->photo)) {
                Storage::disk('public')->delete($model->photo);
            }
        });
    }
}