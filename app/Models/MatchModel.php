<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MatchModel extends Model
{
    use HasFactory;

  

    protected $table = 'vendor_matches';
    protected $primaryKey = 'id';

    protected $fillable = ['id',
        'm5MatchId', 'm5SeriesId', 'm5SeriesName', 'm5Year',
        'm5TeamA', 'm5TeamB', 'm5MatchNo', 'm5StartDate',
        'm5EndDate', 'm5MatchStatusId', 'm5MatchStatus',
        'm5MatchFormat', 'm5MatchResult', 'm5OneBattingTeamName',
        'm5OneScoresFull', 'm5TwoBattingTeamName', 'm5TwoScoresFull',
        'm5GroundName', 'm5TeamAShortName', 'm5TeamBShortName',
        'm5TeamALogo', 'm5TeamBLogo', 'm5MatchStartTimeGMT','m5MatchStartTimeDubai',
        'm5MatchStartTimeLocal', 'isActive','m5SeriesType','m5GenderName',
        'm5Commentary','m5CompetitionName','m5CompetitionId','m5GroundId'
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'match_team');
    }

    public function assignments(): HasMany {
        return $this->hasMany(Assignment::class, 'match_id');
    }

}
