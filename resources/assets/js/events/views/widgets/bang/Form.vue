<template>
    <form
        class="bang-form"
        data-abide2
        novalidate
        data-vv-scope="form-bang"
        @submit.prevent="submit"
    >
        <div class="create-field create-bang-title">
            <input
                v-model="bang.title"
                :placeholder="trans('title')"
                :data-vv-as="trans('title')"
                class="gray-input"
                type="text"
                maxlength="50"
                :class="{'is-invalid-input': errors.has('form-bang.title')}" name="title"
            >
            <span class="form-error" :class="{'is-visible': errors.has('form-bang.title')}">
                {{ errors.first('form-bang.title') }}
            </span>
        </div>

        <div class="create-field create-bang-image">
            <div class="w-photo__tab no-padding">
                <div
                    class="add-photo-btn b-btn__photo-add"
                    @click="photosVisible = true"
                    v-if="!photosVisible"
                    :style="{background: photoBackground}">
                    <svg v-if="!bang.preview_photo" class="icon icon-plus">
                        <use :xlink:href="symbolsSvgUrl('icon-plus')"></use>
                    </svg>
                </div>

                <div v-show="photosVisible" class="w-photo__tab no-padding" id="photo__tab">
                    <div class="w-user__photos w-user__photos--no-height">
                        <button
                            class="b-btn__photo-add user__photos-width"
                            id="addPhotoButton"
                            @click.prevent="loadPhoto">
                            <svg class="icon icon-plus">
                                <use v-bind:xlink:href="symbolsSvgUrl('icon-plus')"></use>
                            </svg>
                        </button>

                        <input
                            type="file"
                            id="newPhotoUpload"
                            class="show-for-sr"
                            name="photo"
                            accept="image/*"
                            ref="photo"
                            v-on:change="uploadProfilePhoto($event, true)">

                        <label
                            class="b-user__photo user__photos-width"
                            v-for="photo in photos"
                            @click="setPhoto(photo)">
                            <img :src="photo.photo_small" :src-big="photo.photo_orig" :id="`profile-photo-${photo.id}`" />
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="create-field create-field-icon create-bang-date">
            <label>
                <svg class="icon">
                    <use :xlink:href="symbolsSvgUrl('icon-calendar_sub')"></use>
                </svg>
                <input
                    v-if="isTouch() && isIos()"
                    v-model="bang.event_date"
                    required
                    type="date"
                    name="event_date"
                    id="event_date"
                    :placeholder="errors.has('form-bang.event_date') ? '' : trans('date')"
                    :data-vv-as="trans('date')"
                    :min="minDate"
                    :max="inAYearDate()"
                    v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: [minDate, inAYearDate(), true]}"
                    class="gray-input ios-date-input"
                    :class="{isTouch: isTouch(), isIos: isIos(), 'datepicker-input-reg': true, 'is-invalid-input': errors.has('form-bang.event_date')}"
                />
                <DatePickerComponent
                    v-else
                    v-model="bang.event_date"
                    name="event_date"
                    required
                    id="event_date"
                    :language="appLanguage"
                    :placeholder="trans('date')"
                    :data-vv-as="trans('date')"
                    :min="minDate"
                    :max="inAYearDate()"
                    v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: [minDate, inAYearDate(), true]}"
                    :input-class="{'gray-input': true, 'datepicker-input-reg': true, 'is-invalid-input': errors.has('form-bang.event_date')}"
                ></DatePickerComponent>
                <span class="form-error" :class="{'is-visible': errors.has('form-bang.event_date')}">
                    {{ errors.first('form-bang.event_date') }}
                </span>
            </label>
        </div>

        <div class="create-field create-field-icon create-bang-time">
            <label>
                <svg class="icon"><use v-bind:xlink:href="symbolsSvgUrl('icon-time')"></use></svg>
                <input
                    class="gray-input"
                    type="text"
                    :placeholder="trans('time')"
                    maxlength="50"
                    :data-vv-as="trans('time')"
                    required
                    v-model="bang.time"
                    v-validate="{required: true}"
                    :class="{'is-invalid-input': errors.has('form-bang.time')}"
                    name="time"
                >
                <span class="form-error" :class="{'is-visible': errors.has('form-bang.time')}">
                    {{ errors.first('form-bang.time') }}
                </span>
            </label>
        </div>

        <div class="create-field create-bang-private">
            <div class="row">
                <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.5 10H21.25V7.5C21.25 4.05 18.45 1.25 15 1.25C11.55 1.25 8.75 4.05 8.75 7.5V10H7.5C6.125 10 5 11.125 5 12.5V25C5 26.375 6.125 27.5 7.5 27.5H22.5C23.875 27.5 25 26.375 25 25V12.5C25 11.125 23.875 10 22.5 10ZM15 21.25C13.625 21.25 12.5 20.125 12.5 18.75C12.5 17.375 13.625 16.25 15 16.25C16.375 16.25 17.5 17.375 17.5 18.75C17.5 20.125 16.375 21.25 15 21.25ZM11.25 10V7.5C11.25 5.425 12.925 3.75 15 3.75C17.075 3.75 18.75 5.425 18.75 7.5V10H11.25Z" fill="#2F7570"/>
                </svg>
                <div class="title private-switch">
                    {{ trans('events.invitation_only_event') }}
                </div>
                <div class="field">
                    <div class="toggle-switch">
                        <input type="checkbox"
                                    id="is_private"
                            v-model="bang.is_private"
                            true-value="1"
                        false-value="0"
                                name="is_private">
                        <label for="is_private">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="create-field create-bang-geo">
            <button
                id="auto-gps"
                :class="[!mapVisible ? 'bb-button-green' : 'bb-button-grey']"
                @click="chooseGps"
                type="button"
            >{{ trans('reg.gps') }}</button>
            <button
                id="auto-map"
                :class="[mapVisible ? 'bb-button-green' : 'bb-button-grey']"
                @click="chooseMap"
                type="button"
            >{{ trans('reg.map') }}</button>
            <span class="form-error" :class="{'is-visible': addressError}">
                {{ addressError }}
            </span>
        </div>

        <div v-if="bang.address || mapVisible" class="create-field create-field-icon create-bang-address">
            <label>
                <svg class="icon">
                    <use :xlink:href="symbolsSvgUrl('icon-location')"></use>
                </svg>
                <div v-if="bang.address && !mapVisible" class="address-value">{{ bang.address }}</div>
                <input
                    v-if="mapVisible"
                    class="gray-input no-swiping"
                    type="text"
                    name="address"
                    :placeholder="trans('event_address')"
                    :data-vv-as="trans('address')"
                    v-model="bang.address"
                    v-validate="'required'"
                    :class="{'is-invalid-input': errors.has('form3.address')}"
                    @input="handleAddressModification"
                    required
                />
            </label>
        </div>

        <div v-if="mapVisible" id="map" class="w-map no-swiping">
            <Map
                :lat="bang.lat"
                :lng="bang.lng"
                :zoom="15"
                styles="height: 300px"
                :clickable="true"
                :draggable="true"
                :dragEnd="handleMapMarkerDrag"/>
        </div>

        <div class="create-field create-bang-address-type">
            <label class="b-checkbox desktop b-checkbox--radio-white">
                <input
                    type="radio"
                    name="address_type"
                    v-model="bang.address_type"
                    value="full_address"
                    required
                />
                <svg class="icon icon-radiobutton_on">
                    <use v-bind:xlink:href="symbolsSvgUrl('icon-radiobutton_on')"></use>
                </svg>
                <div class="text small-text">{{ trans('full_address') }}</div>
            </label><!--b-checkbox-->
            <label class="b-checkbox desktop b-checkbox--radio-white">
                <input
                    type="radio"
                    name="address_type"
                    v-model="bang.address_type"
                    value="city_only"
                    required
                />
                <svg class="icon icon-radiobutton_on">
                    <use v-bind:xlink:href="symbolsSvgUrl('icon-radiobutton_on')"></use>
                </svg>
                <div class="text small-text">{{ trans('city_only') }}</div>
            </label><!--b-checkbox-->
        </div>

        <div class="create-field create-bang-submit">
            <button
                type="button"
                class="bb-button-grey"
                @click="exit"
            >{{ trans('cancel') }}</button>
            <button
                id="create-bang-submit-btn"
                type="button"
                class="bb-button-green"
                @click="submit"
            >{{ trans('save') }}</button>
        </div>
    </form>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';
    import DatePickerComponent from '@buddy/views/widgets/DatePickerComponent.vue';
    import eventsModule from "@events/module/store/type";
    import Map from '@buddy/views/widgets/Map.vue';

    const bangInitial = {
        id: null,
        title: '',
        event_date: '',
        time: '',
        address: '',
        lat: null,
        lng: null,
        location: '',
        locality: '',
        state: '',
        country: '',
        country_code: '',
        address_type: 'full_address',
        preview_photo: null,
    }

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-events').default,
        ],
        components: {
            Map,
            DatePickerComponent
        },
        props: ['eventId'],
        data: function(){
            return {
                bang: _.cloneDeep(bangInitial),
                photosVisible: false,
                mapVisible: false,
                addressError: null,
                slimOptions: this.defaultSlimOptions()
            }
        },
        methods: {
            ...mapActions({
                setBang: eventsModule.actions.setBang,
                saveEvent: eventsModule.actions.events.submit,
            }),
            loadPhoto() {
                this.$refs.photo.click();
            },
            setPhoto(photo) {
                this.bang.preview_photo = _.cloneDeep(photo)
                this.photosVisible = false
            },
            chooseMap() {
                this.mapVisible = true
            },
            async submit() {
                let validated = await this.$validator.validateAll('form-bang')
                if (!validated || !!this.addressError) {
                    return
                }

                const bang = this.bang
                bang.type = 'bang'

                this.showLoadingButton('#create-bang-submit-btn')
                this.saveEvent(event)
                    .then(() => {
                      this.restoreLoadingButton('#create-bang-submit-btn')
                    }).catch(() => {
                  this.restoreLoadingButton('#create-bang-submit-btn')
                })

            },
            async chooseGps() {
                this.mapVisible = false
                let lat, lng, address;
                try {
                    ({lat, lng, address} = await this.getCurrentPositionAndAddress(true))
                    this.bang.lat = lat
                    this.bang.lng = lng
                    this.bang.address = address.formattedAddress
                    this.bang.locality = address.locality
                    this.bang.state = address.state
                    this.bang.country = address.country
                    this.bang.country_code = address.country_code
                    this.addressError = null
                } catch (e) {
                    this.addressError = this.trans('fail_auto_choose_manual_location')
                    this.chooseMap()
                }
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
                    this.bang.lat = newLat;
                    this.bang.lng = newLng;
                    this.bang.location_type = 'manual';
                    this.bang.address = fullInfo.formattedAddress;
                    this.bang.locality = fullInfo.locality;
                    this.bang.state = fullInfo.state;
                    this.bang.country = fullInfo.country;
                    this.bang.country_code = fullInfo.country_code;
                    this.addressError = null
                } catch(e) {
                    this.addressError = e
                }
            },
            handleAddressModification: _.debounce(async function (event){
                let lat, lng;
                if (!event.target.value) {
                    return;
                }

                try {
                    //update map marker
                    let fullInfo = await this.getLatLngForAddress(this.bang.address, true);
                    ({lat, lng} = fullInfo.point);
                    this.bang.lat = parseFloat(lat);
                    this.bang.lng = parseFloat(lng);
                    this.bang.location_type = 'manual';
                    this.bang.locality = fullInfo.locality;
                    this.bang.state = fullInfo.state;
                    this.bang.country = fullInfo.country;
                    this.bang.country_code = fullInfo.country_code;
                    let v = this
                    setTimeout(function() {
                        v.zoom = 15
                    }, 500)
                    this.addressError = null
                } catch(e) {
                    this.addressError = e
                }
            }, 1000),
            reset(){
                this.$validator.reset()

                if (!this.eventIdComputed) {
                    this.resetState(true)

                } else if (this.bangOriginal && this.bangOriginal.id){
                    const resetBang = _.pick(
                        _.cloneDeep(this.bangOriginal),
                        Object.keys(bangInitial)
                    )
                    this.bang = Object.assign(
                        {},
                        resetBang,
                        { preview_photo: this.bangOriginal.photos[0] || null }
                    )
                    console.log('[Bang Form] bang captured', { bang: this.bang, keys: Object.keys(bangInitial) })
                } else {
                    this.bang = _.cloneDeep(bangInitial)
                }
            },
            resetState(resetGPS) {
                console.log('[Bang Form] resetState')

                this.bang = _.cloneDeep(bangInitial)
                this.photosVisible = false
                this.mapVisible = false
                this.addressError = null

                if (resetGPS) {
                    this.chooseGps()
                }
            },
            exit(){
                if (this.eventIdComputed) {
                    this.openEvent(this.eventIdComputed, 'bang')
                } else {
                    this.closeBang()
                }
            }
        },
        watch: {
            bangOriginal: {
                immediate: true,
                handler (value){
                    console.log('[Bang Form] bangOriginal watcher', { value })
                    if (value && value.id) {
                        this.reset()
                    }
                }
            }
        },
        computed: {
            ...mapState({
                photos: 'profilePhotos'
            }),
            ...mapGetters({
                bangData: eventsModule.getters.bang
            }),
            eventIdComputed(){
                let eventId = this.eventId || this.bangData.eventId
                console.log('[Bang Form] eventIdComputed', { eventId })
                return eventId
            },
            bangOriginal(){
                let bang = this.$store.getters.getEvent(this.eventIdComputed)
                console.log('[Bang Form] bangOriginal', { bang })
                return bang
            },
            minDate(){
                return this.bang.id ? this.yesterdayDate() : this.todayDate()
            },
            photoBackground(){
                let preview_photo = this.bang.preview_photo
                if (preview_photo && preview_photo.photo_orig) {
                    return `url(${preview_photo.photo_orig}) center / cover`
                } else {
                    return '#666666';
                }
            }
        },
        mounted() {
            console.log('[Bang Form] Mounted', {
                eventId: this.eventIdComputed,
                bang: this.bangOriginal
            })
            if (!!parseInt(this.eventIdComputed)){
                this.$store.dispatch(eventsModule.actions.events.loadInfo, this.eventIdComputed)
            }
            this.reset()
        },
        activated() {
            console.log('[Bang Form] Activated')
            this.reset()
        }
    }
</script>
