<template>
    <div>
        <div class="b-box" v-show="mapFirstTime">
            <div class="w-form__text text-center">
                {{ trans('select_address') }}
            </div><!--w-form__text-->

            <div class="row align-middle">
                <div :class="{'columns': true, 'small-12': true}" class="loc_buttons">
                    <button class="bb-button-grey" @click="chooseGps" id="auto-location" type="button">{{ trans('reg.gps') }}</button>
                    <button class="bb-button-grey" @click="chooseMap" type="button">{{ trans('reg.map') }}</button>
                </div>
            </div>
        </div>

        <div class="b-box" v-show="!mapFirstTime">
            <div class="w-form__text text-center">
                {{ trans('select_address') }}
            </div><!--w-form__text-->

            <form data-abide2 novalidate data-vv-scope="form3" @submit.prevent="goToStep4">
                <div class="row b-list__block">
                    <div class="small-12 columns">
                        <div class="item-content">
                            <div class="item-title item-title--flexible">
                                {{ trans('address') }}
                            </div>
                            <div class="item-after">
                                <label>
                                    <input class="gray-input no-swiping" type="text" name="address" v-bind:placeholder="trans('event_address')" v-bind:data-vv-as="trans('address')" v-model="address" required v-validate="'required'" :class="{'is-invalid-input': errors.has('form3.address')}" @input="handleAddressModification"/>

                                    <span class="form-error" :class="{'is-visible': errors.has('form3.address')}">
                                      {{ errors.first('form3.address') }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="small-12 columns margin-inputs right text-right" style="margin-top: -10px" v-if="!mapVisible">
                        <button class="bb-button-grey" type="button" @click="showMap">
                            {{ trans('reg.map') }}
                        </button>
                    </div>
                </div>

                <div id="map" class="w-map no-swiping" v-if="mapVisible">
                    <Map
                        :lat="lat"
                        :lng="lng"
                        :zoom="15"
                        styles="height: 300px"
                        :clickable="true"
                        :draggable="true"
                        :dragEnd="handleMapMarkerDrag"
                    />
                </div>

                <div class="row margin-top-15">
                    <div class="small-12 columns w-form__text text-center margin-top-15 margin-bottom-0">
                        {{ trans('display') }}
                    </div><!--w-form__text-->

                    <div class="small-12 columns event-create-radio-container">
                        <label class="b-checkbox desktop b-checkbox--radio-white">
                            <input type="radio" v-validate="'required'" :class="{'is-invalid-input': errors.has('form3.address_type')}" v-model="address_type" value="full_address" required />
                            <svg class="icon icon-radiobutton_on"><use v-bind:xlink:href="symbolsSvgUrl('icon-radiobutton_on')"></use></svg>
                            <div class="text small-text">{{ trans('full_address') }}</div>
                        </label><!--b-checkbox-->

                        <label class="b-checkbox desktop b-checkbox--radio-white">
                            <input type="radio" v-validate="'required'" :class="{'is-invalid-input': errors.has('form3.address_type')}" v-model="address_type" value="city_only" required />
                            <svg class="icon icon-radiobutton_on"><use v-bind:xlink:href="symbolsSvgUrl('icon-radiobutton_on')"></use></svg>
                            <div class="text small-text">{{ trans('city_only') }}</div>
                        </label><!--b-checkbox-->

                        <span class="form-error" :class="{'is-visible': errors.has('form3.address_type')}">
                          {{ errors.first('form3.address_type') }}
                        </span>
                    </div>
                </div><!--row-->

                <div class="row">
                    <div class="small-12 columns w-form__text text-center margin-top-30">
                        {{ trans('location_details') }}
                    </div><!--w-form__text-->

                    <div class="small-12 columns">
                        <label>
                            <input class="gray-input" maxlength="30" type="text" name="location" v-bind:placeholder="trans('location_example')" v-bind:data-vv-as="trans('location_details')" v-model="location" :class="{'is-invalid-input': errors.has('form3.location')}" />

                            <span class="form-error" :class="{'is-visible': errors.has('form3.location')}">
                                  {{ errors.first('form3.location') }}
                                </span>
                        </label>
                    </div>
                </div>

                <div class="row align-middle margin-top-30">
                    <!--<div class="small-3 columns text-left" v-if="isDesktop">-->
                    <!--<button class="b-btn__icon" type="button" @click="goToStep1">-->
                    <!--<i class="fa fa-angle-left" aria-hidden="true"></i>-->
                    <!--</button>-->
                    <!--</div>-->

                    <fieldset class="text-center" :class="{'columns': true, 'small-12': isMobile, 'small-12': isDesktop}">
                        <button class="bb-button-green" :class="{'disabled-element': step4NextDisabled}" type="submit" id="button3">
                            {{ trans('reg.next') }}
                        </button>
                    </fieldset>
                </div>

                <!--<div v-if="isMobile" :class="{'columns': true, 'small-12': isMobile, 'small-3': isDesktop}">-->
                <!--<div class="pull-left">-->
                <!--<a href="javascript:void(0)" @click="goToStep1" class="b-skip__link">{{ trans('reg.back') }}</a>-->
                <!--</div>-->
                <!--</div>-->
            </form>
        </div>
    </div>
</template>

<style scoped>
    .loc_buttons {
        display: flex;
        justify-content: space-around;
        margin-top: 30px;
    }
    .b-list__block .item-content {
        align-items: baseline;
    }
</style>

<script>
    import _ from 'lodash';
    import Map from '@buddy/views/widgets/Map.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['vars'],
        data() {
            return this.vars;
        },
        components: {
            Map
        },
        methods: {
            chooseMap() {
                this.mapFirstTime = false;
                this.showMap();
            },
            showMap() {
                this.mapVisible = true;
            },
            disableNextButton() {
                console.log('[Event Create] disableNextButton')
                this.step4NextDisabled = true
            },
            enableNextButton() {
                console.log('[Event Create] enableNextButton')
                this.step4NextDisabled = false
            },
            async goToStep4() {
                this.showLoadingButton('#button3')
                let validated = await this.$validator.validateAll('form3')
                if (validated) {
                    this.scrollEventsPageTop(1);
                    this.step = 4
                } else {
                    this.scrollEventsPageTop()
                }
                this.restoreLoadingButton('#button3')
            },
            async chooseGps() {
                this.disableNextButton()
                let lat, lng, address
                this.showLoadingButton('#auto-location')
                try {
                    if (window.APP_ENV == 'local') {
                        await this.sleep(1000);
                    }

                    ({lat, lng, address} = await this.getCurrentPositionAndAddress(true));
                    this.lat = lat;
                    this.lng = lng;
                    this.address = address.formattedAddress;
                    this.locality = address.locality;
                    this.state = address.state;
                    this.country = address.country;
                    this.country_code = address.country_code;

                    this.mapFirstTime = false;
                    this.enableNextButton()
                } catch (e) {
                    this.showErrorNotification('fail_auto_choose_manual_location');
                    this.chooseMap();
                }

                this.restoreLoadingButton('#auto-location');
            },
            async handleMapMarkerDrag(event) {
                this.disableNextButton()
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
                    this.address = fullInfo.formattedAddress;
                    this.locality = fullInfo.locality;
                    this.state = fullInfo.state;
                    this.country = fullInfo.country;
                    this.country_code = fullInfo.country_code;

                    this.enableNextButton()
                } catch(e) {
                    this.showErrorNotification(e);
                }
            },
            handleAddressModification: _.debounce(async function (event){
                this.disableNextButton()

                let lat, lng;
                if (!event.target.value) {
                    return;
                }

                try {
                    //update map marker
                    let fullInfo = await this.getLatLngForAddress(this.address, true);
                    ({lat, lng} = fullInfo.point);

                    this.lat = lat;
                    this.lng = lng;
                    this.locality = fullInfo.locality;
                    this.state = fullInfo.state;
                    this.country = fullInfo.country;
                    this.country_code = fullInfo.country_code;

                    this.enableNextButton()
                } catch(e) {
                    this.showErrorNotification(e);
                }
            }, 1000),
        }
    }
</script>
