<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const emits = defineEmits(['toggleLoader'])
const teams = ref([])

onMounted(async () => {
    const { data } = await axios.get('teams')
    teams.value = data.teams
})
</script>

<template>
    <div class="row">
        <div class="col-md-9">
            <div class="p-5 mb-4 bg-light rounded-3">
                <h1 class="display-5 fw-bold">Let's get started!</h1>
                <p class="col-lg-8 fs-4">
                    Every Premier League season comes with it's own story.
                    Join in the action right now by generating league fixtures below.
                    After fixtures are decided, you can simulate each week or entire season.
                </p>
                <button class="btn btn-primary btn-lg" type="button"
                        @click="emits('generateFixtures')">
                    Generate Fixtures
                </button>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header bg-prem text-light">League Teams</div>
                <ul class="list-group list-group-flush">
                    <li
                        v-for="team in teams" :key="team.name"
                        class="list-group-item d-flex align-items-center justify-content-between px-3 py-2">
                        <div>
                            <img :src="team.logo" width="52" :alt="team.name" class="me-3">
                            <span>{{ team.name }}</span>
                        </div>
                        <span class="badge bg-primary">{{ team.rating }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
