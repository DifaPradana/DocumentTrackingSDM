<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $primaryKey = 'role_id';

    public $fillable = [
        'nama_role'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }
}
