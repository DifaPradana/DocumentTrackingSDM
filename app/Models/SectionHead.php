<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionHead extends Model
{
    protected $primaryKey = 'section_head_id';

    protected $fillable = [
        'nama_section_head',
        'departement_id',
        'nama_pjs',
        'tanggal_mulai_pjs',
        'tanggal_akhir_pjs',
    ];

    protected $casts = [
        'tanggal_mulai_pjs' => 'date',
        'tanggal_akhir_pjs' => 'date'
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id', 'departement_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where('nama_section_head', 'like', "%{$search}%")
                ->orWhereHas('departement', function ($sub) use ($search) {
                    $sub->where('nama_departement', 'like', "%{$search}%");
                });
        });
    }
}
