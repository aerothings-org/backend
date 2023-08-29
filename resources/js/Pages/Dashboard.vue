<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import "leaflet/dist/leaflet.css";
import {onMounted, reactive, ref, watchEffect} from "vue";
import Pusher from "pusher-js";
import {LMap, LTileLayer, LMarker, LPopup} from "@vue-leaflet/vue-leaflet";

const telemetry = reactive({
    lat: 0,
    lng: 0,
    alt: 0,
    speed: 0,
    giro: 0,
    temp: 0,
});

const zoom = ref(10);
const center = ref([-27.5705976, -48.799918]);
const markerLatLng = ref([-27.5705976, -48.799918]);

onMounted(() => {
    const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        encrypted: true,
    });
    const channel = pusher.subscribe('swarm-channel');
    channel.bind('telemetry', (e) => {
        telemetry.lat = e.data.lat
        telemetry.lng = e.data.lng
        telemetry.alt = e.data.alt
        telemetry.speed = e.data.speed / 100
        telemetry.giro = e.data.giro / 100
        telemetry.temp = e.data.temp
        markerLatLng.value = [telemetry.lat, telemetry.lng];
        markerLatLng.center = [telemetry.lat, telemetry.lng];
    });
});

</script>

<template>
    <AppLayout title="Dashboard">
        <div class="py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg text-white p-4 justify-center">
                    <div class="w-full h-120">
                        <l-map :useGlobalLeaflet="false" ref="map" :zoom="zoom" :center="center">
                            <l-tile-layer
                                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                layer-type="base"
                                name="OpenStreetMap"
                            ></l-tile-layer>
                            <l-marker v-if="markerLatLng" :lat-lng="markerLatLng">
                                <l-popup>
                                    <p><b>Aerothings</b></p>
                                    <p>lat: {{ telemetry.lat }} | lng: {{ telemetry.lng }}</p>
                                    <p>Speed: {{ telemetry.lat }} </p>
                                    <p>Temp.: {{ telemetry.lat }} </p>
                                    <p>Height: {{ telemetry.lat }}</p>
                                </l-popup>
                            </l-marker>

                        </l-map>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 sm:rounded-lg mt-2">
                    <div class="mx-auto max-w-7xl">
                        <div class="grid grid-cols-1 gap-px bg-white/5 sm:grid-cols-2 lg:grid-cols-4 p-2">
                            <div class="bg-gray-800 px-4 py-6 sm:px-6 lg:px-8 m-1">
                                <p class="text-sm font-medium leading-6 text-gray-400">Speed</p>
                                <p class="mt-2 flex items-baseline gap-x-2">
                                    <span class="text-4xl font-semibold tracking-tight text-white">{{ telemetry.speed }}</span>
                                    <span class="text-sm text-gray-400">km/h</span>
                                </p>
                            </div>
                            <div class="bg-gray-800 px-4 py-6 sm:px-6 lg:px-8 m-1">
                                <p class="text-sm font-medium leading-6 text-gray-400">Temperature</p>
                                <p class="mt-2 flex items-baseline gap-x-2">
                                    <span class="text-4xl font-semibold tracking-tight text-white">{{ telemetry.temp }}</span>
                                    <span class="text-sm text-gray-400">°C</span>
                                </p>
                            </div>
                            <div class="bg-gray-800 px-4 py-6 sm:px-6 lg:px-8 m-1">
                                <p class="text-sm font-medium leading-6 text-gray-400">Gyroscope</p>
                                <p class="mt-2 flex items-baseline gap-x-2">
                                    <span class="text-4xl font-semibold tracking-tight text-white">{{ telemetry.giro }}</span>
                                </p>
                            </div>
                            <div class="bg-gray-800 px-4 py-6 sm:px-6 lg:px-8 m-1">
                                <p class="text-sm font-medium leading-6 text-gray-400">Height</p>
                                <p class="mt-2 flex items-baseline gap-x-2">
                                    <span class="text-4xl font-semibold tracking-tight text-white">{{ telemetry.alt }}</span>
                                    <span class="text-sm text-gray-400">m</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <h1 class="text-white">lat: {{ telemetry.lat }} | lng: {{ telemetry.lng }}</h1>
            </div>
        </div>
    </AppLayout>
</template>

<style>
.h-120 {
    height: 34rem;
}
</style>
