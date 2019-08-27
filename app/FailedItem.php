<?php

namespace App;

use App\Sendout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FailedItem extends Model
{
    use SoftDeletes;

    protected $table = 'failed_items';

    protected $fillable = [
        'failable_id',
        'failable_type'
    ];

    public function failable()
    {
        return $this->morphTo();
    }
}
