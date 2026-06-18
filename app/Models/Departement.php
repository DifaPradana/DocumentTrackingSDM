<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $primaryKey = 'departement_id';

    protected $fillable = [
        'nama_departement'
    ];

    public function documentRoute()
    {
        return $this->hasMany(DocumentRoute::class, 'document_route_id', 'document_route_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('nama_departement', 'like', '%' . $search . '%');
    }
}
