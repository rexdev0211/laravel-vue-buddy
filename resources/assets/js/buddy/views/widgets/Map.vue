<template>
    <div style="width: 100%; height: 100%">
        <gmap-map
            v-if="mapProviderIsGmap"
            :center="{lat: parseFloat(lat || defaultLat), lng: parseFloat(lng || defaultLng) }"
            :zoom="zoom"
            map-type-id="roadmap"
            :style="styles"
            :options="{gestureHandling: 'greedy'}"
            @zoom_changed="zoomChanged"
        >
            <gmap-marker
                v-if="lat && lng"
                :position="{lat: parseFloat(lat || defaultLat), lng: parseFloat(lng || defaultLng) }"
                :clickable="clickable"
                :draggable="draggable"
                @dragend="dragEnd"
            ></gmap-marker>
        </gmap-map>

        <l-map
            v-else
            ref="osmMap"
            :center="{lat: parseFloat(lat || defaultLat), lng: parseFloat(lng || defaultLng) }"
            :zoom="zoom"
            @mapZoom="zoomChanged"
            :style="styles"
            :options="options"
        >
            <l-tile-layer
                url="https://{s}.tile.osm.org/{z}/{x}/{y}.png"
                attribution='&copy; <a href="http://osm.org/copyright">OpenStreetMap</a>'
            ></l-tile-layer>
            <l-marker
                :lat-lng="{lat: parseFloat(lat || defaultLat), lng: parseFloat(lng || defaultLng) }"
                :draggable="draggable"
                @dragend="dragEnd"
            ></l-marker>
        </l-map>
    </div>
</template>

<script>
    import { LMap, LTileLayer, LMarker } from 'vue2-leaflet';
    import 'leaflet/dist/leaflet.css';

    L.Icon.Default.imagePath = '/assets/img/leaflet/';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: {
            lat: [Number, String],
            lng: [Number, String],
            zoom: [Number, String],
            clickable: Boolean,
            draggable: Boolean,
            styles: [String, Object],
            options: [Object],
            dragEnd: {
                type: Function,
                default: function () {}
            },
            zoomChanged: {
                type: Function,
                default: function () {}
            },
        },
        data: () => ({
            defaultLat: 64.1791399,
            defaultLng: -51.7418291,
        }),
        components: {
            LMap,
            LTileLayer,
            LMarker
        }
    }
</script>

<style scoped>

</style>