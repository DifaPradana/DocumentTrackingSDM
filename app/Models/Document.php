<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $primaryKey = 'document_id';

    protected $fillable = [
        'tracking_code',
        'judul_dokumen',
        'priority',
        'current_status',
        'created_by',
        'assigned_to',
        'current_departement_id',
        'deadline'
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function documentRoute()
    {
        return $this->hasMany(DocumentRoute::class, 'document_id', 'document_id');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('judul_dokumen', 'like', '%' . $search . '%');
    }
}
