<template>
  <div class="location-section">
    <form data-abide2 novalidate data-vv-scope="location-form" @submit.prevent="goToStep4">
      <div class="title">
        {{ trans('reg.where_are_you_located') }}
      </div>
      <div class="options">
        <button id="gps" class="btn"
                :class="{'darker': locationType === 'manual' || locationType === '', 'green': locationType === 'gps'}"
                @click="chooseGps"
                type="button">
          {{ trans('reg.gps') }}
        </button>
        <button id="map" class="btn"
                :class="{'darker': locationType === 'gps' || locationType === '', 'green': locationType === 'manual'}"
                @click="setLocationType('manual')"
                type="button">
          {{ trans('reg.map') }}
        </button>
      </div>
      <div class="address-detail location" v-if="addressShow">{{ addressShow }}
        <span class="form-error" :class="{'is-visible': addressError}">
                          {{ addressError }}
                        </span>
      </div>
      <div class="map-section" v-show="mapVisible">
        <div class="address" v-if="mapVisible" :class="{'show': mapVisible}">
          <div class="input-wrapper notranslate">
            <label>
              <input
                  v-show="mapVisible"
                  class="form-control"
                  type="text"
                  name="address"
                  :placeholder="trans('address')"
                  :data-vv-as="trans('address')"
                  v-model="address"
                  v-validate="'required'"
                  :class="{'is-invalid-input': errors.has('location-form.address')}"
                  @input="handleAddressModification"
                  required
              />
              <span class="form-error" v-show="errors.has('location-form.address') || !!addressError" :class="{'is-visible': errors.has('location-form.address') || !!addressError}">
                  {{ errors.first('location-form.address') || addressError }}
              </span>
            </label>
          </div>
          <div v-show="mapVisible" class="map-box" id="map-box">
            <Map
                :lat="lat"
                :lng="lng"
                :zoom="15"
                :styles="mapHeight"
                :clickable="true"
                :draggable="true"
                :dragEnd="handleMapMarkerDrag"/>
            <div class="float-button">
              <button class="btn green set-location" @click="submitLocation">{{ trans('set_location') }}</button>
            </div>
          </div>
        </div>
      </div>
      <div class="continue-button" v-show="locationType">
        <button type="submit" class="btn green" id="button-submit">{{ trans('reg.next') }}</button>
      </div>
    </form>
  </div>
</template>

<script>
import Map from '@buddy/views/widgets/Map.vue';
import _ from "lodash";

export default {
  name: "located",
  mixins: [
    require('@general/lib/mixin').default
  ],
  components: {Map},
  props: ['vars'],
  data() {
    return this.vars;
  },
  computed: {
    mapHeight() {
      if (app.isDesktop) {
        return 'height: 335px'
      } else {
        return 'height: 450px'
      }
    },
  },
  methods: {
    setLocationType(type) {
        this.locationType = type;

        if (type === 'gps') {
            this.chooseGps();
        } else if (type === 'manual') {
            this.mapVisible = true;
        } else {
          return
        }
    },
    handleAddressModification: _.debounce(async function (event){
      let lat, lng;
      if (!event.target.value) {
        return;
      }

      try {
        //update map marker
        this.addressShow = 'Location defining...';
        let fullInfo = await this.getLatLngForAddress(this.address, true);
        this.addressShow = this.address;
        ({lat, lng} = fullInfo.point);
        this.lat = parseFloat(lat);
        this.lng = parseFloat(lng);
        this.location_type = 'manual';
        this.locality = fullInfo.locality;
        this.state = fullInfo.state;
        this.country = fullInfo.country;
        this.country_code = fullInfo.country_code;
        let v = this
        setTimeout(function() {
          v.zoom = 15
        }, 500)
        this.addressError = null
      } catch(e) {
        this.addressError = e
      }
    }, 1000),
    async chooseGps() {
      this.mapVisible = false
      this.locationType = 'gps'
      let lat, lng, address;
      try {
        this.addressShow = 'Location defining...';
        ({lat, lng, address} = await this.getCurrentPositionAndAddress(true))
        this.lat = lat
        this.lng = lng
        this.address = address.formattedAddress
        this.locality = address.locality
        this.location_type = 'automatic'
        this.state = address.state
        this.country = address.country
        this.country_code = address.country_code
        this.addressShow = address.formattedAddress;
        this.addressError = null
      } catch (e) {
        this.addressError = this.trans('fail_auto_choose_manual_location')
        this.chooseMap()
      }
    },
    chooseMap() {
        this.locationType = 'manual';
        this.mapVisible = true;
    },
    async handleMapMarkerDrag(event) {
      let newLat, newLng;

      if (this.mapProviderIsGmap) {
        newLat = event.latLng.lat();
        newLng = event.latLng.lng();
      } else {
        newLat = event.target.getLatLng().lat;
        newLng = event.target.getLatLng().lng;
      }

      try {
        let fullInfo = await this.getAddressForLatLng(newLat, newLng, true);
        this.lat = newLat;
        this.lng = newLng;
        this.location_type = 'manual';
        this.address = fullInfo.formattedAddress;
        this.locality = fullInfo.locality;
        this.state = fullInfo.state;
        this.country = fullInfo.country;
        this.country_code = fullInfo.country_code;
        this.addressError = null
        this.addressShow = fullInfo.formattedAddress;
      } catch(e) {
        this.addressError = e
      }
    },
    async submitLocation() {
        let validate = await this.$validator.validateAll('location-form')
        if (validate) {
          this.mapVisible = false;
        }
    },
    async goToStep4() {
        this.showLoadingButton('#button-submit')
        let validate = await this.$validator.validateAll('location-form')
        if (validate) {
            this.step = 4
        }
        this.restoreLoadingButton('#button-submit')
    }
  }
}
</script>

<style scoped>

</style>