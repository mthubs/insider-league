<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->teams();
    }

    private function season(): Season
    {
        $season = new Season;
        $season->number = 1;
        $season->save();

        return $season;
    }

    private function teams(): array
    {
        $leagueTeams = [
            [ 'name' => 'Man City', 'logo' => 'images/teams/mancity.svg', 'rating' => 95, ],
            [ 'name' => 'Liverpool', 'logo' => 'images/teams/liverpool.svg', 'rating' => 88, ],
            [ 'name' => 'Arsenal', 'logo' => 'images/teams/arsenal.png', 'rating' => 85, ],
            [ 'name' => 'Chelsea', 'logo' => 'images/teams/chelsea.svg', 'rating' => 80, ],
        ];

        $teams = [];

        foreach ($leagueTeams as $team) {
            $newTeam = new Team;
            $newTeam->name = $team['name'];
            $newTeam->logo = $team['logo'];
            $newTeam->rating = $team['rating'];
            $newTeam->save();

            $teams[] = $newTeam;
        }

        return $teams;
    }

}
