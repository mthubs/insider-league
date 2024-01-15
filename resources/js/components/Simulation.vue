<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'

const fixtures = ref([])
const standings = ref([])
const currentWeek = ref({ number: 0, matches: [], played: false })
const seasonEnded = ref(false)

const emits = defineEmits(['resetData', 'toggleLoader']);

const playWeek = async () => {
    const { data } = await axios.post(`/simulate-week/${currentWeek.value.number}`)
    currentWeek.value = data.results
    standings.value = data.standings
    fixtures.value = fixtures.value.map((fixture, index) => {
        if (data.results.number - 1 === index) return data.results.matches
        return fixture
    })
}
const playSeason = async () => {
    emits('toggleLoader', 'Simulating season')
    const { data } = await axios.post(`/simulate-season`)
    currentWeek.value = { number: 6, matches: [], played: true }
    seasonEnded.value = true
    standings.value = data.standings
    fixtures.value = data.fixtures
    emits('toggleLoader')
}

const goNextWeek = () => {
    console.log(fixtures.value)
    const week = fixtures.value.find(fixture => {
        return fixture.find(match => match.played === 0)
    })

    if (!week) {
        currentWeek.value = {
            number: fixtures.value.length,
            matches: fixtures.value[fixtures.value.length - 1],
            played: true
        }
    } else {
        currentWeek.value = { number: week[0].week, matches: week, played: false }
    }

    if (currentWeek.value.played && currentWeek.value.number === 6) seasonEnded.value = true

}


onMounted(async () => {
    const { data } = await axios.get('/simulation');
    fixtures.value = data.fixtures ?? []
    standings.value = data.standings ?? []

    goNextWeek()
});

</script>

<template>
    <div class="row">

        <!-- CONTROLS -->

        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6 d-flex justify-content-center mb-3">
                    <button
                        class="btn btn-primary bg-prem"
                        v-if="currentWeek.played"
                        @click.prevent="goNextWeek"
                            :disabled="seasonEnded"
                    >
                        <span v-if="currentWeek.number === 6">View All Fixtures</span>
                        <span v-else>Go Next Week</span>

                    </button>
                    <button class="btn btn-primary bg-prem" @click.prevent="playWeek" v-else
                        :disabled="seasonEnded"
                    >
                        Play Week
                    </button>
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-between mb-3 flex-lg">
                    <button class="btn btn-primary bg-prem" @click.prevent="playSeason" :disabled="seasonEnded">Play Season</button>
                    <button class="btn btn-danger" @click.prevent="emits('reset-data')">Reset Data</button>
                </div>
            </div>


        </div>

        <!-- FIXTURES -->
        <div class="col-md-6">
            <!-- competition review -->
            <div class="alert alert-light" role="alert" v-if="seasonEnded">
                <p><small>Season has ended. Congrats to <b>{{ standings[0].name }}</b>, our latest champion!</small></p>
                <p class="mb-0"><small>Scroll <b>↓ ↑</b> to view all the results.</small></p>
            </div>
            <!-- competition review -->
            <div class="alert alert-light" role="alert" v-else>
                <p v-if="currentWeek.number < 4"><small>There is only <b>{{ 4 - currentWeek.number }}</b> weeks left for this season's winning predictions.</small></p>
                <p v-else><small>Season <span class="badge bg-primary">predictions</span> are in!</small></p>
                <p><small>Currently, <b>{{ standings[0]?.name }}</b> are leading the race.</small></p>

            </div>

            <!-- matches -->
            <div class="card" v-if="!seasonEnded">
                    <div class="card-header bg-prem text-light text-center">Week {{ currentWeek.number }}</div>
                    <ul class="list-group list-group-flush">
                            <li
                                v-for="match in currentWeek.matches" :key="match.id"
                                class="list-group-item d-flex align-items-center justify-content-between"
                            >
                                <div class="d-flex align-items-center justify-content-end w-100">
                                    <div class="me-3">{{ match.home_team.name }}</div>
                                    <img :src="match.home_team.logo" height="26" :alt="match.home_team.name">
                                </div>
                                <span class="badge bg-primary mx-4" v-if="match.played">
                                    {{ match.home_goals }} <span>-</span> {{ match.away_goals }}
                                </span>
                                <span class="badge bg-primary mx-4" v-else>-</span>
                                <div class="d-flex align-items-center justify-content-start w-100">
                                    <img :src="match.away_team.logo" height="26" :alt="match.away_team.name" class="me-3">
                                    <div>{{ match.away_team.name }}</div>
                                </div>
                            </li>
                    </ul>
                </div>
            <!-- matches -->

            <!-- all fixtures -->

            <div id="season-fixtures" v-if="seasonEnded">
                <div class="card mb-2" v-for="(fixture, index) in fixtures">
                    <div class="card-header bg-success text-light text-center">Week {{ index + 1 }}</div>
                    <ul class="list-group list-group-flush">
                        <li
                            v-for="match in fixture" :key="match.id"
                            class="list-group-item d-flex align-items-center justify-content-between"
                        >
                            <div class="d-flex align-items-center justify-content-end w-100">
                                <div class="me-3">{{ match.home_team.name }}</div>
                                <img :src="match.home_team.logo" height="26" :alt="match.home_team.name">
                            </div>
                            <span class="badge bg-primary mx-4" v-if="match.played">
                                    {{ match.home_goals }} <span>-</span> {{ match.away_goals }}
                                </span>
                            <span class="badge bg-primary mx-4" v-else>-</span>
                            <div class="d-flex align-items-center justify-content-start w-100">
                                <img :src="match.away_team.logo" height="26" :alt="match.away_team.name" class="me-3">
                                <div>{{ match.away_team.name }}</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- LEAGUE TABLE -->

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-prem text-light">League Table</div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Team</th>
                            <th scope="col">%</th>
                            <th scope="col">P</th>
                            <th scope="col">W</th>
                            <th scope="col">D</th>
                            <th scope="col">L</th>
                            <th scope="col">GD</th>
                            <th scope="col">T</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(team, index) in standings" :key="team.id">
                                <td>{{ index + 1 }}<span>.</span></td>
                                <td>
                                    <div>
                                        <img :src="team.logo" width="26" :alt="team.name" class="me-3">
                                        <span>{{ team.name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary" v-if="currentWeek.number > 3 && team.odds !== 0">
                                        <span v-if="team.odds === 100">C</span>
                                        <span v-else>{{ team.odds }}%</span>
                                    </span>
                                </td>
                                <td>{{ team.P }}</td>
                                <td>{{ team.W }}</td>
                                <td>{{ team.D }}</td>
                                <td>{{ team.L }}</td>
                                <td>{{ team.GD }}</td>
                                <td><u><span class="badge bg-dark">{{ team.T }}</span></u></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</template>

<style scoped>
#season-fixtures {
    height: 60vh !important;
    overflow-y: scroll;
}
</style>
