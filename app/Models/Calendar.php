<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $primaryKey = 'calendar_id';

    protected $fillable = [
        'event_title',
        'event_description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_all_day',
        'show_to_all',
        'color',
        'user_id',
        'is_done',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_all_day' => 'boolean',
        'show_to_all' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
