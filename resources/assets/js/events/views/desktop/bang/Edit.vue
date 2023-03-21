<template>
  <div class="secondary-menu create-event" v-if="eventOriginal">
    <div class="secondary-menu-header">
      <i class="back" @click="openEvent(eventOriginal.id, 'bang')"></i>
      <div class="title">{{ trans('edit') }}</div>
      <i class="trash" @click="removeEvent(event.id)"></i>
    </div>
    <div class="secondary-menu-body" id="scroll-event">
      <div class="tab-content-wrapper event-category-tabs"
           :class="{'friends': type === 'friends', 'fun': type === 'fun', 'bang': type === 'bang'}">
        <div class="tab-content-inner">
          <div class="tab-content">
            <div>
              <form novalidate data-vv-scope="form1" @submit.prevent>
                <div class="center">
                  <input type="hidden" name="type" v-model="type" v-validate="{required: true}" />
                  <div class="row">
                    <div class="small-12 form-error text-center" :class="{'is-visible': errors.has('form1.type')}">
                      {{ trans('events.type.required') }}
                    </div>
                    <div class="tabs event-category-tabs">
                      <div class="tab friends"
                           :class="{'active': type === 'friends', '': type !== 'friends'}"
                           @click="setType('friends')">
                        <span>{{ trans('events.type.friends') }}</span>
                      </div>
                      <div class="tab middle fun"
                           :class="{'active': type === 'fun', '': type !== 'fun'}"
                           @click="setType('fun')">
                        <span>{{ trans('events.type.fun') }}</span>
                      </div>
                      <div class="tab last bang"
                           :class="{'active': type === 'bang', '': type !== 'bang'}"
                           @click="setType('bang')">
                        <span>{{ trans('events.type.bang') }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-if="type">
                    <div class="candy-question" v-if="type === 'fun' || type === 'bang'">
                      <div class="checkbox-container">
                        <label class="checkbox-label" :title="trans('events.type.chemsfriendly')">
                          <input type="checkbox" v-model="chemsfriendly">
                          <span class="checkbox-custom"></span>
                          <div class="input-title"></div>
                        </label>
                      </div>
                    </div>

                    <div class="datepicker">
                      <div class="date today"
                           :class="{'active': date === 'today', '': date !== 'today'}"
                           @click="setDate('today')">
                        <span>today</span>
                      </div>
                      <div class="date tomorrow"
                           :class="{'active': date === 'tomorrow', '': date !== 'tomorrow'}"
                           @click="setDate('tomorrow')">
                        <span>tomorrow</span>
                      </div>
                      <div class="date picker">
                        <label>
                          <input class="ios-date-input" required
                                 type="date"
                                 name="event_date"
                                 id="event_date"
                                 v-if="isTouch() && isIos()"
                                 v-bind:placeholder="errors.has('form1.event_date') ? '' : trans('date')"
                                 v-bind:data-vv-as="trans('date')"
                                 v-model="event_date"
                                 v-bind:min="minDate"
                                 v-bind:max="inAYearDate()"
                                 v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: [minDate, inAYearDate(), true]}"
                                 :class="{'datepicker-input-reg': true, 'is-invalid-input': errors.has('form1.event_date')}" />
                          <DatePickerComponent
                              name="event_date" required v-else
                              id="event_date"
                              v-bind:language="appLanguage"
                              v-bind:placeholder="errors.has('form1.event_date') ? '' : trans('date')"
                              v-bind:data-vv-as="trans('date')"
                              v-bind:min="minDate"
                              v-bind:max="inAYearDate()"
                              v-model="event_date"
                              @input="date = 'custom'"
                              v-bind:v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: [minDate, inAYearDate(), true]}"
                              v-bind:input-class="{
													 	'datepicker-input-reg': true,
													 	'is-invalid-input': errors.has('form1.event_date'),
													 	'datepicker-active': date === 'custom'}">
                          </DatePickerComponent>
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="timepicker">
                    <span class="time"></span>
                    <label>
                      <input class="form-control" type="text" v-bind:placeholder="trans('time')" maxlength="50" v-bind:data-vv-as="trans('time')" required v-model="time" v-validate="{required: true}" :class="{'is-invalid-input': errors.has('form1.time')}" name="time">

                      <span class="form-error" :class="{'is-visible': errors.has('form1.time')}">
												{{ errors.first('form1.time') }}
											</span>
                    </label>
                  </div>
                  <div class="name-edit">
                    <label>
                      <input type="text" v-bind:placeholder="trans('title')" maxlength="50" v-bind:data-vv-as="trans('title')" :required="titleRequired" v-model="title" v-validate="{required: titleRequired}" :class="{'is-invalid-input': errors.has('form1.title')}" name="title">

                      <span class="form-error" :class="{'is-visible': errors.has('form1.title')}">
												{{ errors.first('form1.title') }}
											</span>
                    </label>
                  </div>
                  <div class="box add-margin">
                    <div id="event-main-pic" class="profile-main-pic" @click.self="openGallery('clear', true)">
                      <div class="img" v-if="!defaultEventPhoto.match(/default_180x180/)"
                           :style="{'background': defaultEventPhoto}">
                      </div>
                      <div v-if="!defaultEventPhoto.match(/default_180x180/)"
                           @click.stop="clearAvatar('clear', true)" class="close">
                      </div>
                    </div>
                    <div class="media-catalog" v-if="type === 'friends' || type === 'fun'">
                        <PhotoSlider :photos="photos"/>
                        <VideoSlider/>
                        <VideoGallery/>
                    </div>
                    <PhotoGallery/>
                  </div>
                  <div class="box description-link" v-if="type === 'friends' || type === 'fun'">
                    <div class="row">
                      <div class="headline">Description</div>
                      <textarea class="description"
                                maxlength="510"
                                name="description" v-bind:placeholder="trans('event_description')"
                                v-bind:data-vv-as="trans('event_description')" required
                                v-model="description" v-validate="'required'"
                                :class="{'is-invalid-input': errors.has('form1.description')}"
                                rows="3">
											</textarea>

                      <span class="form-error" :class="{'is-visible': errors.has('form1.description')}">
												{{ errors.first('form1.description') }}
											</span>
                    </div>
                    <div class="row">
                      <div class="tags" v-if="type === 'friends' || type === 'fun'">
                        <div class="tag edit notranslate">
                          <input type="text" maxlength="50" v-model='newTag'
                                 class="form-control no-swiping" @keypress.enter.prevent="addTag"
                                 v-bind:placeholder="trans('add_tag')">
                        </div>
                        <div class="tag added" v-if="tags && tags.length"
                             @click="deleteTag(tag)"
                             v-for="tag in tags">
                          <span class="notranslate">{{ tag.name }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="title">Link my profile</div>
                      <div class="field">
                        <div class="toggle-switch">
                          <input type="checkbox" id="link-my-profile" name="push-notifications" checked v-model="is_profile_linked">
                          <label for="link-my-profile">
                            <span class="toggle-track"></span>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="box">
                    <div class="row">
                      <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.5 10H21.25V7.5C21.25 4.05 18.45 1.25 15 1.25C11.55 1.25 8.75 4.05 8.75 7.5V10H7.5C6.125 10 5 11.125 5 12.5V25C5 26.375 6.125 27.5 7.5 27.5H22.5C23.875 27.5 25 26.375 25 25V12.5C25 11.125 23.875 10 22.5 10ZM15 21.25C13.625 21.25 12.5 20.125 12.5 18.75C12.5 17.375 13.625 16.25 15 16.25C16.375 16.25 17.5 17.375 17.5 18.75C17.5 20.125 16.375 21.25 15 21.25ZM11.25 10V7.5C11.25 5.425 12.925 3.75 15 3.75C17.075 3.75 18.75 5.425 18.75 7.5V10H11.25Z" fill="#2F7570"/>
                      </svg>
                      <div class="title private-switch">
                        {{ trans('events.invitation_only_event') }}
                      </div>
                      <div class="field">
                        <div class="toggle-switch">
                          <input type="checkbox" id="is_private"
                                  v-model="is_private"
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

                  <div class="box">
                    <div class="row">
                      <div class="title">{{ trans('location') }}</div>
                      <div class="options">
                        <button id="gps"
                                :class="[!mapVisible ? 'btn' : 'btn darker']"
                                @click="chooseGps"
                                type="button">
                          {{ trans('reg.gps') }}
                        </button>
                        <button id="map"
                                :class="[mapVisible ? 'btn' : 'btn darker']"
                                @click="chooseMap"
                                type="button">
                          {{ trans('reg.map') }}
                        </button>
                      </div>
                      <div class="detail location notranslate" v-if="addressShow">{{ addressShow }}
                        <span class="form-error" :class="{'is-visible': addressError}">
                        {{ addressError }}
                      </span>
                      </div>
                    </div>
                  </div>
                  <div class="address" v-if="mapVisible" :class="{'show': mapVisible}">
                    <div class="input-wrapper">
                      <label>
                        <input
                            v-if="mapVisible"
                            class="form-control"
                            type="text"
                            name="address"
                            :placeholder="trans('event_address')"
                            :data-vv-as="trans('address')"
                            v-model="address"
                            v-validate="'required'"
                            :class="{'is-invalid-input': errors.has('form1.address')}"
                            @input="handleAddressModification"
                            required/>
                      </label>
                    </div>
                    <div v-if="mapVisible" class="map-box" id="map-box">
                      <Map
                          :lat="lat"
                          :lng="lng"
                          :zoom="15"
                          styles="height: 450px"
                          :clickable="true"
                          :draggable="true"
                          :dragEnd="handleMapMarkerDrag"/>
                      <div class="float-button">
                        <button class="btn set-location" @click="setLocation">{{ trans('set_location') }}</button>
                      </div>
                    </div>
                  </div>
                  <button class="btn publish" id="button6" type="button" @click="updateEvent">
                    {{ trans('save') }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
    import {mapGetters, mapState} from 'vuex';
    import DatePickerComponent from '@buddy/views/widgets/DatePickerComponent.vue';
    import eventsModule from "@events/module/store/type";
    import PhotoSlider from '@events/views/widgets/event/create/media/PhotoSlider.vue'
    import VideoSlider from '@events/views/widgets/event/create/media/VideoSlider.vue'
    import PhotoGallery from '@events/views/widgets/event/create/media/PhotoGallery.vue'
    import VideoGallery from '@events/views/widgets/event/create/media/VideoGallery.vue'
    import Map from '@buddy/views/widgets/Map.vue';
    import moment from "moment";

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-events').default,
            require('@general/lib/mixin-event-edit').default
        ],
        data() {
            return {
                id: null,
                title: '',
                event_date: '', //this needs moment globally for vee-validate to work
                time: '',
                description: ``,
                location: '',
                address_type: 'full_address',
                address: '',
                addressShow: '',
                addressError: false,
                tags: [],
                newTag: "",
                type: null,
                chemsfriendly: false,
                is_profile_linked: false,

                lat: 52.520389,
                lng: 13.40424,

                locality: 'Berlin',
                state: 'Berlin',
                country: 'Germany',
                country_code: 'DE',

                //inner components variables
                mapVisible: null,
                mapFirstTime: true,
                //selected photos for event
                selectedPhotos: [],
                selectedVideos: [],
                deletedPhotos: [],
                defaultEventPhoto: 'url("/assets/img/default_180x180.jpg") center / cover',
                photos: [],

                //slim
                croppers: [],
                editPhotoId: null,
                slimOptions: this.defaultSlimOptions(),
                preview_photo: [],
                date: 'today',

                is_private: false,
            }
        },
        components: {
            Map,
            DatePickerComponent,
            PhotoGallery,
            VideoGallery,
            PhotoSlider,
            VideoSlider,
        },
        computed: {
            eventOriginal(){
                return this.$store.getters.getEvent(this.bangData.eventId)
            },
            ...mapGetters({
                bangData: eventsModule.getters.bang
            }),
            defaultAvatar(){
                let user = this.$store.state.profile;
                return user.avatars && user.avatars.default
            },
            adultAvatar(){
                let user = this.$store.state.profile;
                return user.avatars && user.avatars.adult
            },
            titleRequired() {
              return this.type !== 'bang';
            },
            minDate(){
              return this.todayDate();
            },
        },
        mounted() {
            console.log('[Edit Bang mobile] Mounted', {
                eventId: this.eventId,
                event: this.event
            })

            if (!!parseInt(this.eventId)) {
                this.$store.dispatch(eventsModule.actions.events.loadInfo, this.eventId)
            }

            if (!!this.eventOriginal) {
                this.reset()
                this.prepareData(this.eventOriginal);
            }

            this.is_private = this.event.is_private ? 1 : 0;
        },
        methods: {
            changeInvitationOnly(event) {
              let val = event.target.value;
              // ..
            },
            checkDeletePhoto(photoId) {
              const filterId = this.deletedPhotos.filter((filter) => {
                return filter === photoId;
              });

              if (filterId) {
                this.removeByValue(this.deletedPhotos, photoId);
              }
            },
            removeByValue(arr) {
              let what, a = arguments, length = a.length, ax;
              while (length > 1 && arr.length) {
                what = a[--length];
                while ((ax = arr.indexOf(what)) !== -1) {
                  arr.splice(ax, 1);
                }
              }
              return arr;
            },
            prepareData(data) {
                const self = this;
                for (let key in data) {
                  if (key === 'photos') {
                    data[key].forEach(function (photo) {
                      if (data.photo_small === photo.photo_small) {
                        self.defaultEventPhoto = `url(${photo.photo_small}) center / cover`;
                        self.preview_photo = photo;
                      } else {
                        self.photos.push(photo);
                      }
                    });
                  } else {
                    if (key === "event_date") {
                      this.date = "custom";
                    }
                    this[key] = data[key];
                  }
                }
            },
            defaultEventBackground(data) {
              if (data) {
                let photo = data[0];
                this.defaultEventPhoto = `url(${photo.photo_small}) center / cover`
                photo.is_default = 'yes';
                this.preview_photo = photo;
              }
            },
            setLocation() {
              this.mapVisible = false;
            },
            setPhotos(data) {
              this.photos.push(data);
              this.selectedPhotos.push(data.id);
            },
            setDeletePhotos(data, index) {
              this.deletedPhotos.push(data.id);
              this.selectedPhotos.splice(index, 1);
              this.photos.splice(index, 1);
            },
            addTag() {
              let tagValue = _.trimStart(this.newTag, '# ');
              tagValue = _.trimEnd(tagValue, ' ');
              if (!tagValue) {
                return;
              }

              this.newTag = '';
              axios.post('/api/tags/add', {name: tagValue})
                  .then(({data}) => {
                    if (data) {
                      this.user.tags.push(data);
                    }
                  })
            },
            deleteTag(tag) {
              const index = this.user.tags.indexOf(tag);
              const removedArray = this.user.tags.splice(index, 1);
              axios.post('/api/tags/delete', {id: tag.id})
                  .catch((error) => {
                    this.user.tags.splice(index, 0, removedArray[0]);
                  });
            },
            clearAvatar(slot) {
              let photo = this.preview_photo;

              app.$emit('avatar-preloader', { slot, value: true })
              this.defaultEventPhoto = 'url("/assets/img/default_180x180.jpg") center / cover';
              this.preview_photo = [];
              this.deletedPhotos.push(photo.id)
              app.$emit('avatar-preloader', { slot, value: false })
            },
            setType(type) {
              if (this.type !== type) {
                return;
              }
              this.type = type
              if (type !== 'fun' && type !== 'bang') {
                this.chemsfriendly = false
              }
            },
            openGallery(slot, defaultImg = false) {
              console.log(slot)
              app.$emit('show-photo-event-gallery', slot, defaultImg)
            },
            chooseMap() {
              this.mapVisible = true
                this.$nextTick(() => {
                    if (this.isMobile) {
                        document.getElementById('map-box').scrollIntoView();
                    } else {
                        const scrollDiv = document.getElementById('scroll-event');
                        const mapBox = document.getElementById('map-box');
                        const coordinates = scrollDiv.scrollHeight - mapBox.offsetHeight;

                        scrollDiv.scroll({
                          top: coordinates,
                        });
                    }
                });
            },
            async chooseGps() {
              this.mapVisible = false
              let lat, lng, address;
              try {
                ({lat, lng, address} = await this.getCurrentPositionAndAddress(true))
                this.lat = lat
                this.lng = lng
                this.address = address.formattedAddress
                this.locality = address.locality
                this.state = address.state
                this.country = address.country
                this.country_code = address.country_code
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
                this.lat = newLat;
                this.lng = newLng;
                this.location_type = 'manual';
                this.address = fullInfo.formattedAddress;
                this.locality = fullInfo.locality;
                this.state = fullInfo.state;
                this.country = fullInfo.country;
                this.country_code = fullInfo.country_code;
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
            async updateEvent() {
              this.$validator.validate();
              let event = _.pick(this, [
                'id', 'title', 'description', 'event_date', 'type', 'chemsfriendly',
                'is_profile_linked', 'time', 'location', 'locality', 'state',
                'country', 'country_code', 'address', 'lat', 'lng', 'address_type',
                'tags', 'preview_photo', 'deletedPhotos', 'is_private'
              ])
              event.photos = this.selectedPhotos;
              event.videos = this.selectedVideos;
              event.chemsfriendly = event.chemsfriendly ? 1 : 0;

              this.showLoadingButton('#button6')

              this.saveEvent(event)
                  .then(() => {
                    this.restoreLoadingButton('#button6')
                  }).catch(() => {
                this.restoreLoadingButton('#button6')
              })
            },
            setDate(dateType) {
              if (dateType === 'today') {
                this.date = 'today';
                this.event_date = moment().format('YYYY-MM-DD')
              } else if (dateType === 'tomorrow') {
                this.date = 'tomorrow';
                this.event_date = moment().add(1, 'day').format('YYYY-MM-DD')
              }
            }
        }
    }
</script>
