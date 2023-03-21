<template>
    <div>
        <div class="box">
            <div class="checkbox-container">
                <label class="checkbox-label">
                    <input
                        id='auto_loc'
                        v-model="user.location_type"
                        type="radio"
                        name="location_type"
                        value="automatic"
                        @change="setAutomaticLocation">
                    <span class="checkbox-custom"></span>
                    <div class="input-title">{{ trans('automatic_location') }}</div>
                </label>
            </div>
            <div class="checkbox-container">
                <label class="checkbox-label">
                    <input
                        id='manual_loc'
                        v-model="user.location_type"
                        type="radio"
                        name="location_type"
                        value="manual"
                        @click="showMap"
                        @change="saveProfileChange">
                    <span class="checkbox-custom"></span>
                    <div class="input-title">{{ trans('manual_location') }}</div>
                </label>
            </div>
        </div>
        <div class="box">
            <div class="address notranslate">
                <input
                    v-if="!automaticLocation"
                    v-model="user.address"
                    name="address"
                    type="text"
                    class="form-control"
                    v-bind:data-vv-as="trans('address')"
                    v-validate="'required'"
                    @click="showMap"
                    @input="handleAddressModification">
                <span class="form-error" :class="{'is-visible': errors.has('address') || !!addressError}">
                  {{ errors.first('address') || addressError }}
                </span>
            </div>
        </div>
        <div class="map-box" v-if="!automaticLocation && mapVisible">
          <div id="map">
            <Map
                :lat="user.lat"
                :lng="user.lng"
                :zoom="15"
                styles="height: 55em"
                :clickable="true"
                :draggable="true"
                :dragEnd="handleMapMarkerDrag"
            />
          </div>
          <div class="float-button" v-if="!automaticLocation && mapVisible">
            <button class="btn" @click="setLocation">{{ trans('set_location') }}</button>
          </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex';
    import Map from '@buddy/views/widgets/Map.vue';
    import discoverModule from "@discover/module/store/type";

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-discover').default
        ],
        components: {
            Map
        },
        data() {
            return {
                mapVisible: false,
                addressError: null,
            }
        },
        computed: {
            ...mapState({
                user: 'profile',
                locationUpdating: 'locationUpdating'
            }),
            automaticLocation(){
                return this.user.location_type === 'automatic'
            }
        },
        methods: {
            ...mapActions([
                'forceUpdateLocation'
            ]),
            submit(){

            },
            cancel(){

            },
            setLocation() {
                this.mapVisible = false;
            },
            async handleMapMarkerDrag(event) {
                this.$store.dispatch(discoverModule.actions.users.setRefreshQueued, true)

                let newLat, newLng;
                if (this.mapProviderIsGmap) {
                    newLat = event.latLng.lat();
                    newLng = event.latLng.lng();
                } else {
                    newLat = event.target.getLatLng().lat;
                    newLng = event.target.getLatLng().lng;
                }

                try {
                    let fullAddress = await this.getAddressForLatLng(newLat, newLng, true);
                    await this.updateUserLocation({
                        address: fullAddress.formattedAddress,
                        lat: newLat,
                        lng: newLng,
                        address_lat: newLat,
                        address_lng: newLng,
                        location_type: 'manual',
                        locality: fullAddress.locality,
                        state: fullAddress.state,
                        country: fullAddress.country,
                        country_code: fullAddress.country_code
                    });
                } catch(e) {
                    this.showErrorNotification(e);
                }
            },
            handleAddressModification: _.debounce(async function (event){
                console.log('[Geo] Changing address...')

                let lat, lng;
                if (!event.target.value) {
                    return;
                }

                try {
                    //update map marker
                    let fullAddress = await this.getLatLngForAddress(this.user.address, true);
                    console.log('[Geo] Detected address', fullAddress);
                    ({lat, lng} = fullAddress.point);
                    console.log('[Geo] Detected coords', {lat, lng});

                    await this.updateUserLocation({
                        address: this.user.address,
                        lat,
                        lng,
                        address_lat: lat,
                        address_lng: lng,
                        location_type: 'manual',
                        locality: fullAddress.locality,
                        state: fullAddress.state,
                        country: fullAddress.country,
                        country_code: fullAddress.country_code
                    })

                    this.addressError = null

                    this.$store.dispatch(discoverModule.actions.users.reload);
                    app.$emit('reload-events')
                    app.$emit('reload-clubs')
                } catch(e) {
                    this.addressError = e
                }
            }, 1000),
            async setAutomaticLocation() {
                await this.updateUserLocation({
                    location_type: 'automatic',
                })
                this.forceUpdateLocation()
            },
            showMap() {
                this.mapVisible = true

                this.$emit('refreshScroll');
            }
        }
    }
</script>
<style scoped lang="scss">
.map-box {
  position: relative;
}
</style>