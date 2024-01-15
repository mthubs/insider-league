<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GameController extends Controller
{
    public function index()
    {
        $season = Season::activeSeason();

        if (!$season) {
            $lastSeason = Season::latest()->first();

            $season = new Season;
            $season->number = $lastSeason ? $lastSeason->number + 1 : 1;
            $season->status = 'ONGOING';
            $season->save();

            return response()->json([
                'step' => 'GENERATE_FIXTURES',
            ]);
        }

        $schedule = $season->fixturesWithTeams();

        if (!count($schedule)) {
            return response()->json(['step' => 'GENERATE_FIXTURES']);
        }

        $played = $schedule->where('played', 1)->first();

        $step = $played ? 'VIEW_SIMULATION' : 'VIEW_FIXTURES';

        return response()->json([
            'step' => $step,
        ]);

    }

    public function teams()
    {
        $teams = Team::orderBy('rating', 'desc')->get();

        return response()->json(['teams' => $teams]);
    }

    public function generateFixtures()
    {
        $season = Season::activeSeason();
        $season->deleteFixtures();
        $season->generateFixtures();

        // For better immersion
        sleep(2);

        return response()->json(['step' => 'VIEW_FIXTURES']);

    }

    public function fixtures()
    {
        $season = Season::activeSeason();
        $fixtures = Fixture::withTeams($season->id);
        $fixtures = Fixture::groupByWeek($fixtures);

        return response()->json([
            'fixtures' => $fixtures
        ]);
    }

    public function simulation()
    {
        $season = Season::activeSeason();

        $fixtures = Fixture::withTeams($season->id);
        $fixturesGrouped = Fixture::groupByWeek($fixtures);

        $standingsAndOdds = $this->calculateStandingsAndOdds($season, $fixtures);

        return response()->json([
            'fixtures' => $fixturesGrouped,
            'standings' => $standingsAndOdds['standings'],
            'odds' => $standingsAndOdds['odds'],
        ]);
    }

    public function simulateWeek(int $week)
    {
        $season = Season::activeSeason();
        $weekFixtures = Fixture::where('season_id', $season->id)->where('week', $week)->with(['homeTeam', 'awayTeam'])->get();

        foreach ($weekFixtures as $fixture) {

            $diff = $this->calcRoundPercentageDiff($fixture->homeTeam->rating, $fixture->awayTeam->rating);

            $chances = $this->calculateTeamChances($diff, [$fixture->homeTeam->rating, $fixture->awayTeam->rating]);

            $scores = $this->simulateScores($chances, 15);

            $fixture->home_goals = $scores[0];
            $fixture->away_goals = $scores[1];
            $fixture->played = true;
            $fixture->save();
        }

        $standingsAndOdds = $this->calculateStandingsAndOdds($season, $season->fixturesWithTeams());

        return response()->json([
            'results' => [
                'number' => $week,
                'matches' => $weekFixtures,
                'played' => true,
            ],
            'standings' => $standingsAndOdds['standings'],
            'odds' => $standingsAndOdds['odds'],
        ]);
    }

    public function simulateSeason()
    {
        $season = Season::activeSeason();
        $fixtures = $season->fixturesWithTeams();

        foreach ($fixtures as $fixture) {

            $diff = $this->calcRoundPercentageDiff($fixture->homeTeam->rating, $fixture->awayTeam->rating);

            $chances = $this->calculateTeamChances($diff, [$fixture->homeTeam->rating, $fixture->awayTeam->rating]);

            $scores = $this->simulateScores($chances, 15);

            $fixture->home_goals = $scores[0];
            $fixture->away_goals = $scores[1];
            $fixture->played = true;
            $fixture->save();
        }

        $standingsAndOdds = $this->calculateStandingsAndOdds($season, $fixtures);

        sleep(2);

        return response()->json([
            'fixtures' => Fixture::groupByWeek($fixtures),
            'standings' => $standingsAndOdds['standings'],
            'odds' => $standingsAndOdds['odds'],
        ]);
    }

    public function resetData()
    {
        Fixture::truncate();
        Season::truncate();

        sleep(2);

        return response()->json([
            'step' => 'GENERATE_FIXTURES'
        ]);
    }

    #region HELPER METHODS

    private function calculateStandings(Season $season): array
    {
        $teams = Team::all();
        $fixtures = Fixture::where('season_id', $season->id)->where('played', 1)->get();

        $standings = [];

        foreach ($teams as $team) {
            $standings[$team->id] = [
                'id' => $team->id,
                'name' => $team->name,
                'logo' => $team->logo,
                'P' => 0,
                'W' => 0,
                'D' => 0,
                'L' => 0,
                'GD' => 0,
                'T' => 0,
            ];
        }

        foreach ($fixtures as $fixture) {
            $standings[$fixture['home_team_id']]['P'] += 1;
            $standings[$fixture['away_team_id']]['P'] += 1;

            $standings[$fixture['home_team_id']]['GD'] += $fixture['home_goals'] - $fixture['away_goals'];
            $standings[$fixture['away_team_id']]['GD'] += $fixture['away_goals'] - $fixture['home_goals'];


            if ($fixture['home_goals'] > $fixture['away_goals']) {
                $standings[$fixture['home_team_id']]['W'] += 1;
                $standings[$fixture['away_team_id']]['L'] += 1;

                $standings[$fixture['home_team_id']]['T'] += 3;
            }

            if ($fixture['home_goals'] < $fixture['away_goals']) {
                $standings[$fixture['home_team_id']]['L'] += 1;
                $standings[$fixture['away_team_id']]['W'] += 1;

                $standings[$fixture['away_team_id']]['T'] += 3;
            }

            if ($fixture['home_goals'] === $fixture['away_goals']) {
                $standings[$fixture['home_team_id']]['D'] += 1;
                $standings[$fixture['away_team_id']]['D'] += 1;

                $standings[$fixture['home_team_id']]['T'] += 1;
                $standings[$fixture['away_team_id']]['T'] += 1;
            }

        }

        return $standings;
    }

    private function calcRoundPercentageDiff($n1, $n2): int|float
    {
        $n1 += 5;
        $diff = (($n1 - $n2) / (($n1 + $n2) / 2)) * 100;
        return round($diff);
    }

    private function calculateTeamChances ($diff, $ratings): array
    {
        if ($ratings[0] === $ratings[1]) return [50, 50];

        if ($ratings[0] > $ratings[1]) {
            return [
                (100 / 2) + ($diff / 2),
                (100 / 2) - ($diff / 2),
            ];
        }

        return [
            (100 / 2) - ($diff / 2),
            (100 / 2) + ($diff / 2),
        ];
    }

    private function playGoalPossession($chances): array
    {
        $rand = rand(1, 3000);

        if ($rand <= ($chances[0] * 10))
            return [1, 0];
        else if ($rand > ($chances[0] * 10) && $rand <= ($chances[0] * 10) + ($chances[1] * 10))
            return [0, 1];
        else
            return [0, 0];
    }

    private function simulateScores($chances, $possessions): array
    {
        $score = [0, 0];
        for ($i = 0; $i < $possessions; $i++) {
            $goals = $this->playGoalPossession($chances);
            $score[0] += $goals[0];
            $score[1] += $goals[1];
        }
        return $score;
    }

    private function calculateLeagueOdds($teamPoints, $weeksLeft): array
    {
        $pointsForWin = 3;

        arsort($teamPoints);

        $maxPoints = count($teamPoints) * $pointsForWin;

        $odds = [];

        foreach ($teamPoints as $team => $points) {
            if ($points + ($weeksLeft * $pointsForWin) >= reset($teamPoints)) {
                $possiblePoints = $points + ($weeksLeft * $pointsForWin);

                $teamOdds = $possiblePoints / $maxPoints;

                $odds[$team] = $teamOdds;
            } else {
                $odds[$team] = 0;
            }
        }

        $sumOdds = array_sum($odds);

        $normalizedOdds = [];

        foreach ($odds as $team => $teamOdds) {
            $normalizedOdds[$team] = round(($teamOdds / $sumOdds) * 100, 1);
        }

        return $normalizedOdds;
    }

    private function calculateStandingsAndOdds($season, $fixtures): array
    {
        $notPlayed = $fixtures->where('played', 0)->first();
        $currentWeek = $notPlayed ? $notPlayed->week : 6;

        $standings = $this->calculateStandings($season);


        $teamPoints = array_map(function ($team) {
            return $team['id'] = $team['T'];
        }, $standings);

        $odds = $this->calculateLeagueOdds($teamPoints, 6 - $currentWeek);

        foreach ($odds as $key => $odd) {
            $standings[$key]['odds'] = $odd;
        }

        $standings = collect($standings);
        $sorted = $standings->sortBy([
            ['T', 'desc'],
            ['GD', 'desc']
        ]);

        return [
            'standings' => $sorted->values()->all(),
            'odds' => $odds,
        ];
    }

    #endregion
}
