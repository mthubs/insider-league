<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use function Psy\sh;

class Season extends Model
{
    use HasFactory;

    public static function activeSeason(): null|Season
    {
        return Season::where('status', 'ONGOING')->latest()->first();
    }

    public function generateFixtures()
    {
        try {
            DB::beginTransaction();

            $teams = Team::pluck('id')->toArray();

            $schedule = $this->generateRoundRobinSchedule(array_values($teams));
            $fixtures = [];

            foreach ($schedule as $key => $week) {
                $weekFixtures = [];

                foreach ($week as $match) {
                    $weekFixtures[] = [
                        'season_id' => $this->id,
                        'week' => $key + 1,
                        'home_team_id' => $match[0],
                        'away_team_id' => $match[1],
                    ];
                }

                $fixtures[] = $weekFixtures;
            }

            foreach ($fixtures as $week) {
                $weekFixtures = [];

                foreach ($week as $match) {
                    $weekFixtures[] = [
                        'season_id' => $match['season_id'],
                        'week' => $match['week'] + 3,
                        'home_team_id' => $match['away_team_id'],
                        'away_team_id' => $match['home_team_id'],
                    ];

                }

                $fixtures[] = $weekFixtures;
            }

            foreach ($fixtures as $week) {
                foreach ($week as $game) {
                    Fixture::create($game);
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $exception) {
            DB::rollBack();
            return $exception;
        }
    }

    public function fixtures()
    {
        return $this->hasMany(Fixture::class);
    }

    public function fixturesWithTeams()
    {
        return Fixture::where('season_id', $this->id)->with(['homeTeam', 'awayTeam'])->get();
    }

    public function deleteFixtures()
    {
        Fixture::where('season_id', $this->id)->truncate();
    }

    private function generateRoundRobinSchedule($teams) {
        $numTeams = count($teams);
        $numRounds = $numTeams - 1;

        $schedule = [];

        for ($round = 1; $round <= $numRounds; $round++) {
            $roundMatches = [];

            for ($i = 0; $i < $numTeams / 2; $i++) {
                $team1 = $teams[$i];
                $team2 = $teams[$numTeams - 1 - $i];

                $roundMatches[] = [$team1, $team2];
            }

            shuffle($roundMatches);

            $schedule[] = $roundMatches;

            // Rotate teams for the next round
            $lastTeam = array_pop($teams);
            array_splice($teams, 1, 0, $lastTeam);
        }

        return $schedule;
    }
}
