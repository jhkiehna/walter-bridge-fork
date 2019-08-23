<?php

namespace App;

use App\Call;
use App\Sendout;
use App\Interview;
use App\CandidateCoded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'walter_id',
        'central_id',
    ];

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'central_id', 'central_id');
    }

    public function sendouts()
    {
        return $this->hasMany(Sendout::class, 'central_id', 'central_id');
    }

    public function candidatesCoded()
    {
        return $this->hasMany(CandidateCoded::class, 'central_id', 'central_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class, 'central_id', 'central_id');
    }
}
