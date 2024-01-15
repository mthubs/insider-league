<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fixture extends Model
{
    use HasFactory;

    protected $guarded;

    public static function withTeams($seasonId)
    {
        return Fixture::where('season_id', $seasonId)->with(['homeTeam', 'awayTeam'])->get();
    }
    /**
     * @param Collection<Fixture> $fixtures
     */
    public static function groupByWeek(Collection $fixtures): array
    {
        $fixtures = $fixtures->groupBy('week');

        $fixturesToArray = [];

        foreach ($fixtures as $fixture) {
            $fixturesToArray[] = array_values($fixture->toArray());
        }

        return $fixturesToArray;
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function homeTeam(): HasOne
    {
        return $this->hasOne(Team::class, 'id', 'home_team_id');
    }

    public function awayTeam(): HasOne
    {
        return $this->hasOne(Team::class, 'id', 'away_team_id');
    }
}
