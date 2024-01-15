<script setup>
import axios from 'axios'
import { ref, onMounted } from 'vue'

const fixtures = ref([])

const emits = defineEmits(['startSimulation'])

onMounted(async () => {
    const { data } = await axios.get('/fixtures');
    fixtures.value = data.fixtures ?? []
});
</script>

<template>
    <div class="w-100 text-center pt-2 pb-1">
        <h1 class="fs-4 mb-0 pb-0">Fixtures</h1>
    </div>
    <hr class="w-25 mx-auto">
    <div class="row">
        <div class="col-lg-4 mb-4" v-for="(fixture, index) in fixtures" :key="fixture.id">
            <div class="card">
                <div class="card-header bg-prem text-light text-center">Week {{ index + 1 }}</div>
                <ul class="list-group list-group-flush">
                    <li
                        v-for="match in fixture" :key="match.id"
                        class="list-group-item d-flex align-items-center justify-content-between"
                    >
                        <div class="d-flex align-items-center justify-content-end w-100">
                            <div class="me-3">{{ match.home_team.name }}</div>
                            <img :src="match.home_team.logo" width="26" :alt="match.home_team.name">
                        </div>
                        <span class="mx-4">-</span>
                        <div class="d-flex align-items-center justify-content-start w-100">
                            <img :src="match.away_team.logo" width="26" :alt="match.away_team.name" class="me-3">
                            <div>{{ match.away_team.name }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <template v-if="fixtures.length">
        <hr class="w-25 mx-auto mb-4">
        <div class="d-flex justify-content-center">
            <button class="btn btn-success btn-lg" type="button" @click.prevent="emits('startSimulation')">
                Start Simulation
            </button>
        </div>
    </template>
</template>

<style scoped>

</style>
