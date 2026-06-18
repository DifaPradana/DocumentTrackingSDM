<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRoute extends Model
{
    public $primaryKey = 'document_route_id';

    public $fillable = [
        'document_id',
        'departement_id',
        'urutan',
        'status',
        'note'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id', 'document_id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id', 'departement_id');
    }
}
