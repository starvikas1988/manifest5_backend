<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MatchModel extends Model
{
    use HasFactory;

    protected $table = 'matches';

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'match_team');
    }

    public function assignments(): HasMany {
        return $this->hasMany(Assignment::class, 'match_id');
    }

}
