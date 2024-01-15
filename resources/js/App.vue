<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Loader from "./components/Loader.vue";
import Header from "./components/Header.vue";
import Home from "./components/Home.vue";
import Fixtures from "./components/Fixtures.vue";
import Simulation from "./components/Simulation.vue";

const show = ref(false)
const loaderHeading = ref('Loading')
const simBrain = ref({
    currentStep: ''
})

const toggleLoader = (text = 'Loading', value) => {
    show.value = value ?? !show.value
    loaderHeading.value = text
}

const generateFixtures = async () => {
    toggleLoader('Generating fixtures')
    const { data } = await axios.get('generate-fixtures')
    simBrain.value.currentStep = data.step
    toggleLoader()
}

const startSimulation = () => {
    toggleLoader('Initiating Simulation')
    setTimeout(() => {
        simBrain.value.currentStep = 'VIEW_SIMULATION'
        toggleLoader()
    }, 2000)
}

const resetData = async () => {
    const confirm = window.confirm('Are you sure you want to reset data?')
    if (!confirm) return
    toggleLoader('Flushing season data')
    const { data } = await axios.delete('reset-data')
    simBrain.value.currentStep = data.step
    toggleLoader()
}

onMounted(async () => {
    const { data } = await axios.get('app')
    simBrain.value.currentStep = data.step
})

</script>

<template>
    <Loader :show="show" :heading="loaderHeading" />

    <div class="container py-4">
        <Header />

        <main>
            <Home @generate-fixtures="generateFixtures" v-if="simBrain.currentStep === 'GENERATE_FIXTURES'" />
            <Fixtures @start-simulation="startSimulation" v-if="simBrain.currentStep === 'VIEW_FIXTURES'" />
            <Simulation @toggle-loader="toggleLoader" @reset-data="resetData" v-if="simBrain.currentStep === 'VIEW_SIMULATION'" />
        </main>

    </div>

</template>
