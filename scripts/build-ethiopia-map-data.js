import fs from 'fs';
import path from 'path';
import area from '@turf/area';

const rawDir = path.join(process.cwd(), 'storage/app/geo/raw');
const outDir = path.join(process.cwd(), 'public/data');

const adminFiles = {
    1: 'ethiopia_admin_level_1_gcs.geojson',
    2: 'ethiopia_admin_level_2_gcs.geojson',
    3: 'ethiopia_admin_level_3_gcs_simplified.geojson',
};

const populationByRegion = {
    'Addis Ababa': 3945000,
    Afar: 2076000,
    Amhara: 23216000,
    'Benishangul Gumz': 1251000,
    'Dire Dawa': 551000,
    Gambela: 525000,
    Harari: 283000,
    Oromia: 40884000,
    SNNP: 13044044,
    Sidama: 5301868,
    Somali: 6657000,
    'South West Ethiopia': 4197164,
    Tigray: 5838000,
};

const metricMeta = {
    population: {
        label: 'Population',
    },
    registered: {
        label: 'Registered voters',
    },
    votes: {
        label: 'Votes cast',
    },
    turnout: {
        label: 'Turnout %',
    },
    stations: {
        label: 'Polling stations',
    },
};

const hashString = (value) => {
    let hash = 0;
    for (let i = 0; i < value.length; i += 1) {
        hash = (hash << 5) - hash + value.charCodeAt(i);
        hash |= 0;
    }
    return Math.abs(hash);
};

const seeded = (value) => (hashString(value) % 1000) / 1000;

const vary = (base, amplitude, seed) => {
    const scale = seeded(seed) * 2 - 1;
    return base + scale * amplitude;
};

const loadGeojson = (level) => {
    const file = adminFiles[level];
    const data = JSON.parse(fs.readFileSync(path.join(rawDir, file), 'utf8'));
    return data;
};

const withArea = (feature) => ({
    ...feature,
    properties: {
        ...feature.properties,
        _area: area(feature),
    },
});

const normalizeWeights = (features, weightKey) => {
    const total = features.reduce((sum, feature) => sum + feature.properties[weightKey], 0);
    return features.map((feature) => ({
        ...feature,
        properties: {
            ...feature.properties,
            [weightKey]: total > 0 ? feature.properties[weightKey] / total : 0,
        },
    }));
};

const computeRegionMetrics = (name, population) => {
    const registeredRate = Math.min(Math.max(vary(0.58, 0.06, `${name}-reg`), 0.48), 0.7);
    const turnout = Math.min(Math.max(vary(68, 7, `${name}-turnout`), 55), 82);
    const registered = Math.round(population * registeredRate);
    const votes = Math.round((registered * turnout) / 100);
    const stations = Math.max(120, Math.round(population / vary(2600, 320, `${name}-stations`)));

    return {
        population: Math.round(population),
        registered,
        votes,
        turnout: Math.round(turnout * 10) / 10,
        stations,
    };
};

const slimFeature = (feature, level) => {
    const props = feature.properties;

    return {
        type: 'Feature',
        geometry: feature.geometry,
        properties: {
            level,
            name: props.name,
            pcode: props.pcode,
            parent: props.parent || null,
            adm1: props.adm1 || props.name,
            adm2: props.adm2 || null,
            population: props.population,
            registered: props.registered,
            votes: props.votes,
            turnout: props.turnout,
            stations: props.stations,
        },
    };
};

const buildAdmin1 = (data) => {
    const features = data.features.map((feature) => {
        const name = feature.properties.ADM1_EN;
        const population = populationByRegion[name] || 500000;
        const metrics = computeRegionMetrics(name, population);
        return {
            ...feature,
            properties: {
                name,
                pcode: feature.properties.ADM1_PCODE,
                ...metrics,
            },
        };
    });

    return {
        type: 'FeatureCollection',
        metadata: {
            level: 1,
            metrics: metricMeta,
            source: 'Ethiopia admin boundaries (Ethiopian Statistical Service, via EthioSATHub) with 2023 regional population estimates.',
        },
        features: features.map((feature) => slimFeature(feature, 1)),
    };
};

const buildAdmin2 = (data, admin1Lookup) => {
    const enriched = data.features.map(withArea);
    const grouped = new Map();

    enriched.forEach((feature) => {
        const key = feature.properties.ADM1_PCODE;
        if (!grouped.has(key)) grouped.set(key, []);
        grouped.get(key).push(feature);
    });

    const features = [];

    grouped.forEach((group, key) => {
        const parent = admin1Lookup.get(key);
        const basePop = parent?.population || 100000;

        const weighted = group.map((feature) => {
            const weight = feature.properties._area * vary(1, 0.15, feature.properties.ADM2_PCODE);
            return {
                ...feature,
                properties: {
                    ...feature.properties,
                    _weight: Math.max(weight, 0.000001),
                },
            };
        });

        const normalized = normalizeWeights(weighted, '_weight');

        normalized.forEach((feature) => {
            const name = feature.properties.ADM2_EN;
            const population = basePop * feature.properties._weight;
            const metrics = computeRegionMetrics(name, population);

            features.push({
                ...feature,
                properties: {
                    name,
                    pcode: feature.properties.ADM2_PCODE,
                    parent: key,
                    adm1: feature.properties.ADM1_EN,
                    ...metrics,
                },
            });
        });
    });

    return {
        type: 'FeatureCollection',
        metadata: {
            level: 2,
            metrics: metricMeta,
            source: 'Zone-level boundaries with metrics allocated from regional totals by area weighting.',
        },
        features: features.map((feature) => slimFeature(feature, 2)),
    };
};

const buildAdmin3 = (data, admin2Lookup) => {
    const enriched = data.features.map(withArea);
    const grouped = new Map();

    enriched.forEach((feature) => {
        const key = feature.properties.ADM2_PCODE;
        if (!grouped.has(key)) grouped.set(key, []);
        grouped.get(key).push(feature);
    });

    const features = [];

    grouped.forEach((group, key) => {
        const parent = admin2Lookup.get(key);
        const basePop = parent?.population || 50000;

        const weighted = group.map((feature) => {
            const weight = feature.properties._area * vary(1, 0.2, feature.properties.ADM3_PCODE);
            return {
                ...feature,
                properties: {
                    ...feature.properties,
                    _weight: Math.max(weight, 0.000001),
                },
            };
        });

        const normalized = normalizeWeights(weighted, '_weight');

        normalized.forEach((feature) => {
            const name = feature.properties.ADM3_EN;
            const population = basePop * feature.properties._weight;
            const metrics = computeRegionMetrics(name, population);

            features.push({
                ...feature,
                properties: {
                    name,
                    pcode: feature.properties.ADM3_PCODE,
                    parent: key,
                    adm1: feature.properties.ADM1_EN,
                    adm2: feature.properties.ADM2_EN,
                    ...metrics,
                },
            });
        });
    });

    return {
        type: 'FeatureCollection',
        metadata: {
            level: 3,
            metrics: metricMeta,
            source: 'Woreda boundaries with metrics allocated from zone totals by area weighting.',
        },
        features: features.map((feature) => slimFeature(feature, 3)),
    };
};

const writeGeojson = (name, data) => {
    const filePath = path.join(outDir, name);
    fs.writeFileSync(filePath, JSON.stringify(data));
    console.log(`Wrote ${name} (${data.features.length} features)`);
};

const main = () => {
    if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true });

    const admin1Raw = loadGeojson(1);
    const admin2Raw = loadGeojson(2);
    const admin3Raw = loadGeojson(3);

    const admin1 = buildAdmin1(admin1Raw);
    const admin1Lookup = new Map(admin1.features.map((feature) => [feature.properties.pcode, feature.properties]));

    const admin2 = buildAdmin2(admin2Raw, admin1Lookup);
    const admin2Lookup = new Map(admin2.features.map((feature) => [feature.properties.pcode, feature.properties]));

    const admin3 = buildAdmin3(admin3Raw, admin2Lookup);

    writeGeojson('ethiopia-admin1.geojson', admin1);
    writeGeojson('ethiopia-admin2.geojson', admin2);
    writeGeojson('ethiopia-admin3.geojson', admin3);
};

main();
