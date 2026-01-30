#!/usr/bin/env sh
set -e

RAW_DIR="storage/app/geo/raw"

mkdir -p "$RAW_DIR"

curl -L https://ethiosathub.com/data/ethiopia_admin_level_1_gcs.geojson -o "$RAW_DIR/ethiopia_admin_level_1_gcs.geojson"
curl -L https://ethiosathub.com/data/ethiopia_admin_level_2_gcs.geojson -o "$RAW_DIR/ethiopia_admin_level_2_gcs.geojson"
curl -L https://ethiosathub.com/data/ethiopia_admin_level_3_gcs_simplified.geojson -o "$RAW_DIR/ethiopia_admin_level_3_gcs_simplified.geojson"

echo "Downloaded GeoJSON files to $RAW_DIR"
