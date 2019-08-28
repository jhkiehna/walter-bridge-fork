<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'central_id',
        'intranet_user_id',
        'stats_call_id',
        'valid',
        'dialed_number',
        'type',
        'duration',
        'date',
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
