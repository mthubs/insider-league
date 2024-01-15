<script setup>
import {ref} from "vue"

const props = defineProps({ show: Boolean, heading: String })

const getRandomInt = (min, max) => {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min) + min);
}

const facts = [
    "Manchester City scored the highest amount of goals in one season; 106 goals (2017/18).",
    "Only six teams have played in the Premier League for every season: Arsenal, Chelsea, Everton, Liverpool, Man United and Spurs.",
    "The biggest ever Premier League win came in 1995 when Manchester United beat Ipswich Town 9-0.",
    "Ryan Giggs has 13 Premier League winner’s medals.",
    "Sadio Mané scored a hat-trick (3 goals) in 2 mins 56 seconds for Southampton against Aston Villa in 2015.",
    "In 2008-09, goalkeeper Edwin Van der Sar played 14 consecutive matches without letting in a goal.",
    "Ole Gunnar Solskjaer once came on as a substitute in the 72nd minute for Man United and scored four goals.",
    "Up until January 2018, players from 97 different countries have scored in the Premier League. The top-scoring countries, in order, are England, France, Ireland, Scotland and The Netherlands. Nations with one goa l include Estonia and Burundi.",
    "The highest Premier League single match attendance was the Tottenham Hotspur versus Arsenal match with 83,222 attendees on 10 February 2018.",
]

const quote = ref(facts[getRandomInt(0, facts.length)])

setInterval(() => {
    quote.value = facts[getRandomInt(0, facts.length)]
}, 10000);

</script>

<template>
    <div
        id="overlay"
        :class="{ 'visible': show, 'invisible': !show }"
        class="d-flex flex-column align-items-center justify-content-start"
    >
        <div class="px-3 py-2 d-flex align-items-center justify-content-center bg-light shadow w-100">
            <div class="spinner-border text-primary me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div>{{ heading }}<span>...</span></div>
        </div>
        <div class="px-3 py-2 text-center bg-success text-light w-100">
            <span>
                {{ quote }}
            </span>
        </div>
    </div>
</template>

<style scoped>
#overlay {
    position: fixed;
    display: block;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0,0,0,0.7);
    z-index: 2;
    cursor: progress;
}

.invisible {
    visibility: hidden;
}

.visible {
    visibility: visible;
}
</style>
