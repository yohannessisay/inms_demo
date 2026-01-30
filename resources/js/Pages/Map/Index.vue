<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const mapEl = ref(null);
const levelKey = ref(1);
const metricKey = ref('population');
const selectedFeature = ref(null);
const loading = ref(false);

const levelOptions = [
    { key: 1, label: 'Regions' },
    { key: 2, label: 'Zones' },
    { key: 3, label: 'Woredas' },
];

const metricOptions = [
    { key: 'population', label: 'Population' },
    { key: 'registered', label: 'Registered' },
    { key: 'votes', label: 'Votes' },
    { key: 'turnout', label: 'Turnout %' },
    { key: 'stations', label: 'Stations' },
];

const metricColors = {
    population: '#0f766e',
    registered: '#3b82f6',
    votes: '#f59e0b',
    turnout: '#10b981',
    stations: '#f97316',
};

const geoData = ref({});
const meta = ref({});

let map = null;
let geoLayer = null;
let outlineLayer = null;
let pinnedPopup = null;
let selectedLayer = null;

const formatNumber = (value) => {
    if (value === null || value === undefined) return 'N/A';
    if (metricKey.value === 'turnout') return `${value.toLocaleString()}%`;
    return value.toLocaleString();
};

const summary = computed(() => {
    const data = geoData.value[levelKey.value];
    if (!data) return null;

    const features = data.features;
    const count = features.length;
    const totalPopulation = features.reduce((sum, f) => sum + f.properties.population, 0);
    const totalRegistered = features.reduce((sum, f) => sum + f.properties.registered, 0);
    const totalVotes = features.reduce((sum, f) => sum + f.properties.votes, 0);
    const totalStations = features.reduce((sum, f) => sum + f.properties.stations, 0);
    const avgTurnout =
        features.reduce((sum, f) => sum + f.properties.turnout, 0) / count;

    return {
        count,
        totalPopulation,
        totalRegistered,
        totalVotes,
        totalStations,
        avgTurnout: Math.round(avgTurnout * 10) / 10,
    };
});

const metricRange = computed(() => {
    const data = geoData.value[levelKey.value];
    if (!data) return { min: 0, max: 0 };
    const values = data.features.map((f) => f.properties[metricKey.value]);
    return {
        min: Math.min(...values),
        max: Math.max(...values),
    };
});

const getColor = (value) => {
    const { min, max } = metricRange.value;
    const ratio = (value - min) / (max - min || 1);
    const hue = 180 - ratio * 140;
    return `hsl(${hue}, 75%, 55%)`;
};

const createOutline = (data) => {
    if (outlineLayer) {
        outlineLayer.remove();
    }

    outlineLayer = L.geoJSON(data, {
        style: {
            color: '#0f172a',
            weight: 2.5,
            fillOpacity: 0,
        },
    }).addTo(map);
};

const getLocationParts = (feature) => {
    const region = feature.properties.adm1 || feature.properties.name;
    const zone = feature.properties.adm2 || (feature.properties.level === 2 ? feature.properties.name : null);
    const woreda = feature.properties.level === 3 ? feature.properties.name : null;

    return { region, zone, woreda };
};

const buildTooltipHtml = (feature) => {
    const { region, zone, woreda } = getLocationParts(feature);

    return `
        <div class="map-tooltip">
            <div class="map-tooltip-title">${feature.properties.name}</div>
            <div class="map-tooltip-subtitle">
                ${region ? `<div>Region: ${region}</div>` : ''}
                ${zone ? `<div>Zone: ${zone}</div>` : ''}
                ${woreda ? `<div>Woreda: ${woreda}</div>` : ''}
            </div>
            <div class="map-tooltip-value">${metricKey.value.replace('_', ' ')}: ${formatNumber(feature.properties[metricKey.value])}</div>
        </div>
    `;
};

const buildPopupHtml = (feature) => {
    const { region, zone, woreda } = getLocationParts(feature);

    return `
        <div class="map-label map-label--pin">
            <div class="map-label-tag">Pinned</div>
            <div class="map-label-title">${feature.properties.name}</div>
            <div class="map-label-subtitle">
                ${region ? `<div>Region: ${region}</div>` : ''}
                ${zone ? `<div>Zone: ${zone}</div>` : ''}
                ${woreda ? `<div>Woreda: ${woreda}</div>` : ''}
            </div>
            <div class="map-label-value">${metricKey.value.replace('_', ' ')}: ${formatNumber(feature.properties[metricKey.value])}</div>
            <div class="map-label-grid">
                <div><span class="dot" style="background:${metricColors.population}"></span>${feature.properties.population.toLocaleString()} population</div>
                <div><span class="dot" style="background:${metricColors.registered}"></span>${feature.properties.registered.toLocaleString()} registered</div>
                <div><span class="dot" style="background:${metricColors.votes}"></span>${feature.properties.votes.toLocaleString()} votes</div>
                <div><span class="dot" style="background:${metricColors.turnout}"></span>${feature.properties.turnout}% turnout</div>
                <div><span class="dot" style="background:${metricColors.stations}"></span>${feature.properties.stations.toLocaleString()} stations</div>
            </div>
        </div>
    `;
};

const setPinnedPopup = (latlng, feature) => {
    if (pinnedPopup) {
        pinnedPopup.remove();
        pinnedPopup = null;
    }

    pinnedPopup = L.popup({
        closeButton: false,
        autoClose: false,
        closeOnClick: false,
        className: 'map-popup',
        offset: [0, -12],
    })
        .setLatLng(latlng)
        .setContent(buildPopupHtml(feature))
        .addTo(map);
};

const highlightStyle = {
    weight: 2.5,
    color: '#0f172a',
    fillOpacity: 0.85,
};

const selectedStyle = {
    weight: 3.5,
    color: '#0f172a',
    fillOpacity: 0.95,
    dashArray: '2 3',
};

const dimStyle = {
    weight: 0.8,
    color: '#0f172a',
    fillOpacity: 0.25,
};

const baseStyle = (feature) => ({
    weight: 1,
    color: '#0f172a',
    fillColor: getColor(feature.properties[metricKey.value]),
    fillOpacity: 0.65,
});

const clearSelection = () => {
    selectedFeature.value = null;
    selectedLayer = null;
    if (geoLayer) {
        geoLayer.setStyle(baseStyle);
    }
    if (pinnedPopup) {
        pinnedPopup.remove();
        pinnedPopup = null;
    }
};

const applySelection = (layer, feature, latlng) => {
    selectedFeature.value = feature;
    selectedLayer = layer;
    geoLayer.eachLayer((item) => item.setStyle(dimStyle));
    layer.setStyle({
        ...selectedStyle,
        fillColor: getColor(feature.properties[metricKey.value]),
    });
    layer.bringToFront();
    setPinnedPopup(latlng, feature);
};

const renderLayer = (data) => {
    if (geoLayer) {
        geoLayer.remove();
    }

    geoLayer = L.geoJSON(data, {
        style: baseStyle,
        onEachFeature: (feature, layer) => {
            layer.bindTooltip(buildTooltipHtml(feature), {
                sticky: true,
                direction: 'top',
                className: 'map-tooltip-wrapper',
                opacity: 1,
            });
            layer.on('mouseover', () => {
                if (selectedLayer && selectedLayer !== layer) return;
                layer.setStyle(highlightStyle);
            });
            layer.on('mouseout', () => {
                if (selectedLayer) {
                    if (selectedLayer === layer) {
                        layer.setStyle({
                            ...selectedStyle,
                            fillColor: getColor(feature.properties[metricKey.value]),
                        });
                    }
                    return;
                }
                geoLayer.resetStyle(layer);
            });
            layer.on('click', (event) => {
                if (event.originalEvent) {
                    L.DomEvent.stopPropagation(event.originalEvent);
                }
                applySelection(layer, feature, event.latlng);
            });
        },
    }).addTo(map);

    createOutline(data);

    map.fitBounds(geoLayer.getBounds(), { padding: [40, 40] });
};

const loadGeoData = async (level) => {
    if (geoData.value[level]) return geoData.value[level];
    loading.value = true;
    const response = await fetch(`/data/ethiopia-admin${level}.geojson`);
    const data = await response.json();
    geoData.value = { ...geoData.value, [level]: data };
    meta.value = { ...meta.value, [level]: data.metadata };
    loading.value = false;
    return data;
};

const applyLevel = async () => {
    const data = await loadGeoData(levelKey.value);
    renderLayer(data);
    clearSelection();
};

const applyMetric = () => {
    if (!geoLayer) return;
    geoLayer.setStyle(baseStyle);
    geoLayer.eachLayer((layer) => {
        if (layer.getTooltip()) {
            layer.setTooltipContent(buildTooltipHtml(layer.feature));
        }
    });
    if (selectedFeature.value) {
        const layer = geoLayer
            .getLayers()
            .find((item) => item.feature.properties.pcode === selectedFeature.value.properties.pcode);
        if (layer) {
            applySelection(layer, selectedFeature.value, layer.getBounds().getCenter());
        }
    }
};

onMounted(async () => {
    map = L.map(mapEl.value, {
        zoomControl: false,
        attributionControl: false,
        dragging: true,
        scrollWheelZoom: true,
        doubleClickZoom: false,
    });

    await applyLevel();

    map.on('click', () => {
        clearSelection();
    });
});

watch(levelKey, () => {
    applyLevel();
});

watch(metricKey, () => {
    applyMetric();
});

onBeforeUnmount(() => {
    if (map) map.remove();
});
</script>

<template>
    <Head title="Election Map" />

    <AuthenticatedLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                    GIS Prototype
                </p>
                <h1 class="font-display text-3xl font-semibold">
                    Ethiopia Election Map
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="rounded-full border border-white/60 bg-white/70 px-2 py-1 shadow-sm">
                    <div class="flex gap-1">
                        <Button
                            v-for="option in levelOptions"
                            :key="option.key"
                            size="sm"
                            :variant="levelKey === option.key ? 'primary' : 'ghost'"
                            @click="levelKey = option.key"
                        >
                            {{ option.label }}
                        </Button>
                    </div>
                </div>
                <div class="rounded-full border border-white/60 bg-white/70 px-2 py-1 shadow-sm">
                    <div class="flex gap-1">
                        <Button
                            v-for="option in metricOptions"
                            :key="option.key"
                            size="sm"
                            :variant="metricKey === option.key ? 'primary' : 'ghost'"
                            @click="metricKey = option.key"
                        >
                            {{ option.label }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="map-shell relative overflow-hidden rounded-[32px] border border-white/60 shadow-[0_35px_80px_rgba(15,23,42,0.18)]">
                <div class="absolute left-6 top-6 z-[500] rounded-2xl border border-white/60 bg-white/85 px-4 py-3 text-xs font-semibold text-slate-700 shadow-lg">
                    Hover to preview, click to pin a label on the map.
                </div>
                <div class="absolute right-6 top-6 z-[500] w-64 rounded-2xl border border-white/60 bg-white/85 px-4 py-3 text-xs text-slate-600 shadow-lg">
                    <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                        Coverage Summary
                    </div>
                    <p class="mt-2 text-sm font-semibold text-slate-800">
                        {{ summary?.count || 0 }} {{ levelOptions.find((l) => l.key === levelKey)?.label.toLowerCase() }} mapped
                    </p>
                    <div v-if="summary" class="mt-3 space-y-1 text-xs">
                        <div class="flex items-center justify-between">
                            <span>Population</span>
                            <span class="font-semibold">{{ summary.totalPopulation.toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Registered</span>
                            <span class="font-semibold">{{ summary.totalRegistered.toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Votes</span>
                            <span class="font-semibold">{{ summary.totalVotes.toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Stations</span>
                            <span class="font-semibold">{{ summary.totalStations.toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Avg turnout</span>
                            <span class="font-semibold">{{ summary.avgTurnout }}%</span>
                        </div>
                    </div>
                </div>
                <div class="absolute bottom-6 left-6 z-[500] rounded-2xl border border-white/60 bg-white/85 px-4 py-3 text-xs text-slate-600 shadow-lg">
                    <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                        Legend
                    </div>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="legend-bar"></div>
                        <div class="text-[10px] text-slate-500">
                            <div>Low</div>
                            <div class="mt-4">High</div>
                        </div>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500">
                        {{ metricOptions.find((m) => m.key === metricKey)?.label }} distribution
                    </p>
                </div>
                <div class="absolute bottom-6 right-6 z-[500] rounded-2xl border border-white/60 bg-white/85 px-4 py-3 text-xs text-slate-600 shadow-lg">
                    <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                        Active Metric
                    </div>
                    <p class="mt-2 text-sm font-semibold text-slate-800">
                        {{ metricOptions.find((m) => m.key === metricKey)?.label }}
                    </p>
                    <p class="mt-1 text-xs">
                        {{ meta[levelKey]?.source }}
                    </p>
                    <p class="mt-2 text-[11px] text-slate-500">
                        Lower-level metrics are modeled estimates allocated by area weighting.
                    </p>
                </div>

                <div v-if="loading" class="absolute inset-0 z-[600] grid place-items-center bg-white/60 backdrop-blur">
                    <div class="rounded-2xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white">
                        Loading map data...
                    </div>
                </div>

                <div ref="mapEl" class="h-[620px] w-full"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
