<script setup>
import { ref } from 'vue'
import Loader from "./components/Loader.vue";

const show = ref(false)
const loaderHeading = ref('Loading')

const toggleLoader = (text = 'Loading', timer = 3) => {
    show.value = true
    loaderHeading.value = text
    setTimeout(() => show.value = false, timer * 1000)
}
</script>

<template>
    <Loader :show="show" :heading="loaderHeading" />

    <div class="container py-4">
        <header class="pb-3 mb-4 border-bottom">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <img src="/favicon.png" height="52" alt="prem icon">
                </div>
                <div>
                    <h1 class="fs-4 mb-0 pb-0">Premier League</h1>
                    <span>
                        <small class="text-muted">Holders - Man City</small>
                    </span>
                </div>
            </div>
        </header>

        <main>
            <button
                type="button"
                @click="toggleLoader('Testing loader', 5)"
                class="btn btn-success"
            >
                Counter is: {{ counter }}
            </button>
        </main>

    </div>

</template>
