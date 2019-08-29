<?php

namespace App;

use App\User;
use App\FailedItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sendout extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'central_id',
        'walter_consultant_id',
        'walter_sendout_id',
        'date'
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
