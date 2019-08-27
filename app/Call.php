<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'central_id',
        'valid',
        'date',
        'type',
        'trunk',
        'duration',
        'areacode',
        'dialed_number',
        'phone_number',
        'extension',
        'city',
        'state',
        'long_distance',
        'international',
        'local',
        'department',
        'department_id',
        'raw',
        'first_name',
        'last_name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'central_id', 'central_id');
    }

    public function failedItems()
    {
        return $this->morphMany(FailedItem::class, 'failable');
    }
}
