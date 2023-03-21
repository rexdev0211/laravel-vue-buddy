<template>
  <div class="w-views">
    <div class="secondary-menu create-event">
      <div class="secondary-menu-header">
        <i class="back" @click="goTo(type === 'bang' ? `/bang/${eventId}`: `/event/${eventId}`)"></i>
        <div class="title">{{ trans('edit') }}</div>
        <i class="trash" @click="removeEvent(eventId)"></i>
      </div>
      <div class="secondary-menu-body">
        <div class="tab-content-wrapper event-category-tabs"
             :class="{'guide': type === 'guide', 'friends': type === 'friends', 'fun': type === 'fun', 'bang': type === 'bang'}">
          <div class="tab-content-inner">
            <div class="tab-content">
              <div>
                <form data-abide2 novalidate data-vv-scope="form1">
                  <div class="center">
                    <input type="hidden" name="type" v-model="type" v-validate="{required: true}" />
                    <div class="row">
                      <div class="small-12 form-error text-center" :class="{'is-visible': errors.has('form1.type')}">
                        {{ trans('events.type.required') }}
                      </div>
                      <div class="tabs event-category-tabs">
                        <div class="tab guide notranslate"
                             :class="{'active': type === 'guide', '': type !== 'friends'}"
                             @click="setType('guide')">
                          <span>Guide</span>
                        </div>
                        <div class="tab middle fun notranslate"
                             :class="{'active': type === 'fun', '': type !== 'fun'}"
                             @click="setType('fun')">
                          <span>{{ trans('events.type.fun') }}</span>
                        </div>
                        <div class="tab last bang notranslate"
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
                          <span>{{ trans('events.date.today') }}</span>
                        </div>
                        <div class="date tomorrow"
                             :class="{'active': date === 'tomorrow', '': date !== 'tomorrow'}"
                             @click="setDate('tomorrow')">
                          <span>{{ trans('events.date.tomorrow') }}</span>
                        </div>
                        <div class="date picker">
                          <label>
                            <input class="ios-date-input" required
                                   type="date"
                                   name="event_date"
                                   id="event_date"
                                   v-if="isTouch() && isIos()"
                                   v-bind:placeholder="errors.has('form1.event_date') ? '' : trans('events.date.pick_date')"
                                   v-bind:data-vv-as="trans('events.date.pick_date')"
                                   v-model="event_date"
                                   v-bind:min="todayDate()"
                                   v-bind:max="inAYearDate()"
                                   v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: [todayDate(), inAYearDate(), true]}"
                                   :class="{'datepicker-input-reg': true, 'is-invalid-input': errors.has('form1.event_date')}" />
                            <DatePickerComponent
                                name="event_date" required v-else
                                id="event_date"
                                v-bind:language="appLanguage"
                                v-bind:placeholder="errors.has('form1.event_date') ? '' : trans('events.date.pick_date')"
                                v-bind:data-vv-as="trans('events.date.pick_date')"
                                v-bind:min="todayDate()"
                                v-bind:max="inAYearDate()"
                                v-model="event_date"
                                @input="date = 'custom'"
                                v-bind:v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: [todayDate(), inAYearDate(), true]}"
                                v-bind:input-class="{
                                  'datepicker-input-reg': true,
                                  'is-invalid-input': errors.has('form1.event_date'),
                                  'datepicker-active': date === 'custom'
                                }">
                            </DatePickerComponent>
                          </label>
                        </div>
                      </div>
                      <div class="timepicker">
                        <span class="time"></span>
                        <label>
                          <TimePickerComponent
                              v-if="type === 'guide'"
                              required
                              name="time"
                              v-bind:centered="true"
                              v-bind:toRight="true"
                              v-bind:data-vv-as="trans('time')"
                              v-bind:v-validate="{required: true, date_format: 'HH:mm'}"
                              v-model="time"
                          >
                          </TimePickerComponent>
                          <input v-if="type !== 'guide'" class="form-control" type="text" v-bind:placeholder="trans('time')" maxlength="50" v-bind:data-vv-as="trans('time')" required v-model="time" v-validate="{required: true}" :class="{'is-invalid-input': errors.has('form1.time')}" name="time">

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
                        <div class="media-catalog">
                          <template v-if="type === 'guide' || type === 'fun'">
                            <PhotoSlider :photos="selectedPhotos"/>
                            <VideoSlider :videos="selectedVideos"/>
                            <VideoGallery ref="VideoGallery"/>
                          </template>
                          <PhotoGallery ref="PhotoGallery"/>
                        </div>
                      </div>
                      <div class="box description-link" v-if="type === 'guide' || type === 'fun'">
                        <div class="row">
                          <div class="headline" v-if="type === 'fun' || type === 'bang'">{{ trans('event_description') }}</div>
                          <div class="headline" v-if="type === 'guide'">Description</div>
                          <textarea class="description" v-if="type === 'fun' || type === 'bang'" rows="1" placeholder="What do you want to do?" v-model="description"></textarea>
                          <vue-editor
                              v-if="type === 'guide'"
                              v-model="description"
                              v-bind:placeholder="textAreaDescription"
                              class="form-control no-swiping description"
                              aria-valuemax="3000"
                              :editor-toolbar="customToolbar"
                              v-validate="{required: true, max: 3000}"
                              name="description"
                              :class="{'is-invalid-input': errors.has('form1.description')}"
                          >
                          </vue-editor>
                          <span class="form-error" :class="{'is-visible': errors.has('form1.description')}">
												    {{ errors.first('form1.description') }}
											    </span>
                        </div>
                        <div class="row" v-if="type === 'fun'">
                          <div class="tags">
                            <div class="tag edit notranslate">
                              <input type="text" maxlength="50" v-model='newTag'
                                      class="form-control no-swiping" @keyup.enter.prevent="addTag"
                                      v-bind:placeholder="trans('add_tag')">
                            </div>
                            <div class="tag added" v-if="tags && tags.length"
                                  @click="deleteTag(tag)"
                                  v-for="tag in tags">
                              <span class="notranslate">{{ tag.name }}</span>
                            </div>
                          </div>
                        </div>
                        <div class="row" v-if="type === 'fun'">
                          <div class="title">{{ trans('events.link_my_profile') }}</div>
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

                      <div class="box" v-if="type === 'bang'">
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
                                    v-model='is_private'
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

                      <div class="website" v-if="type === 'guide'">
                        <span class="website-icon"></span>
                        <label>
                          <input type="text" placeholder="web address" v-bind:data-vv-as="trans('website')" v-model="website" v-validate="{max: 750}" :class="{'is-invalid-input': errors.has('form1.website')}" name="website">

                          <span class="form-error" :class="{'is-visible': errors.has('form1.website')}">
                              {{ errors.first('form1.website') }}
                          </span>
                        </label>
                      </div>
                      <div class="box">
                        <div class="row">
                          <div class="title">{{ trans('location') }}</div>
                          <div class="options" v-if="type === 'bang' || type === 'fun'">
                            <button id="gps" class="btn"
                                    :class="{'darker': manualLocationSet}"
                                    @click="chooseGps"
                                    type="button">
                              {{ trans('reg.gps') }}
                            </button>
                            <button id="map" class="btn"
                                    :class="{'darker': !manualLocationSet}"
                                    @click="chooseMap"
                                    type="button">
                              {{ trans('reg.map') }}
                            </button>
                          </div>
                          <div class="detail location notranslate" v-if="(addressShow || addressError) && type !== 'guide'">{{ addressShow }}
                            <span class="form-error" :class="{'is-visible': addressError}">
                              {{ addressError }}
                            </span>
                          </div>
                        </div>
                      </div>
                      <div class="venue" v-if="type === 'guide'">
                        <label>
                          <input type="text" v-bind:placeholder="trans('venue')" maxlength="30" v-bind:data-vv-as="trans('venue')" required v-model="venue" v-validate="{required: true}" :class="{'is-invalid-input': errors.has('form1.venue')}" name="venue">

                          <span class="form-error" :class="{'is-visible': errors.has('form1.venue')}">
								            {{ errors.first('form1.venue') }}
							            </span>
                        </label>
                      </div>
                      <div class="detail location notranslate" v-if="addressShow && type === 'guide'">
                        {{ addressShow }}
                        <span class="form-error" :class="{'is-visible': addressError}">
                            {{ addressError }}
                        </span>
                      </div>
                      <div id="map-box" class="address" v-if="mapVisible || type === 'guide'" :class="{'show': mapVisible || type === 'guide'}">
                        <div class="input-wrapper">
                          <label>
                            <input
                                v-show="mapVisible || type === 'guide'"
                                class="form-control"
                                type="text"
                                name="address"
                                :placeholder="trans('event_address')"
                                :data-vv-as="trans('address')"
                                v-model="address"
                                @click="mapVisible = true"
                                v-validate="'required'"
                                :class="{'is-invalid-input': errors.has('form1.address')}"
                                @input="handleAddressModification"
                                required
                            />
                          </label>
                        </div>
                        <div v-show="mapVisible" class="map-box" :style="{'height': type === 'guide' ? '265px' : '450px'}">
                          <Map
                              :lat="lat"
                              :lng="lng"
                              :zoom="15"
                              :styles="mapHeight"
                              :clickable="true"
                              :draggable="true"
                              :options="mapOptions"
                              :dragEnd="handleMapMarkerDrag"/>
                          <div class="float-button">
                            <button type="button" class="btn set-location" @click="setLocation">{{ trans('set_location') }}</button>
                          </div>
                        </div>
                      </div>
                      <div v-if="type === 'guide'" class="box optional-info">
                        <div class="row">
                          <div class="title">
                            Optional information
                          </div>
                        </div>
                      </div>
                      <div class="optional-info-name" v-if="type === 'guide'">
                        <label>
                          <input type="text" v-bind:placeholder="trans('name')" v-bind:data-vv-as="trans('name')" v-model="name" v-validate="{max: 30}" :class="{'is-invalid-input': errors.has('form1.name')}" name="name">

                          <span class="form-error" :class="{'is-visible': errors.has('form1.name')}">
								              {{ errors.first('form1.name') }}
							            </span>
                        </label>
                      </div>
                      <div class="contact-info" v-if="type === 'guide'">
                        <label>
                          <input type="text" placeholder="contact info (email / phone)" v-bind:data-vv-as="trans('contact')" v-model="contact" v-validate="{max: 30}" :class="{'is-invalid-input': errors.has('form1.contact')}" name="contact">

                          <span class="form-error" :class="{'is-visible': errors.has('form1.contact')}">
								              {{ errors.first('form1.contact') }}
							            </span>
                        </label>
                      </div>
                      <div class="optional-info-note" v-if="type === 'guide'">
                        <label>
                          <input type="text" placeholder="Note to us" v-bind:data-vv-as="trans('note')" v-model="note" v-validate="{max: 300}" :class="{'is-invalid-input': errors.has('form1.note')}" name="note">

                          <span class="form-error" :class="{'is-visible': errors.has('form1.note')}">
								              {{ errors.first('form1.note') }}
							            </span>
                        </label>
                      </div>
                      <button class="btn publish" id="button6" type="button" @click="updateEvent">{{ trans('save') }}</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!--secondary-menu-->
  </div><!--w-views-->
</template>


<script>
    import TimePickerComponent from "@buddy/views/widgets/TimePickerComponent.vue";
    import PhotoSlider from '@events/views/widgets/event/create/media/PhotoSlider.vue'
    import VideoSlider from '@events/views/widgets/event/create/media/VideoSlider.vue'
    import PhotoGallery from '@events/views/widgets/event/create/media/PhotoGallery.vue'
    import VideoGallery from '@events/views/widgets/event/create/media/VideoGallery.vue'
    import _ from "lodash";
    import DatePickerComponent from '@buddy/views/widgets/DatePickerComponent.vue';
	  import eventsModule from '@events/module/store/type';
	  import Map from '@buddy/views/widgets/Map.vue';
    import moment from "moment";

    import { VueEditor, Quill } from "vue2-editor";

	  export default {
	      mixins: [
	          require('@general/lib/mixin').default,
			      require('@general/lib/mixin-events').default,
			      require('@general/lib/mixin-event-edit').default,
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
                newTag: '',
                type: null,
                chemsfriendly: false,
                is_profile_linked: false,
                is_private: false,

                lat: 52.520389,
                lng: 13.40424,

                website: '',
                venue: '',
                name: '',
                contact: '',
                note: '',

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
                deletedVideos: [],
                defaultEventPhoto: 'url("/assets/img/default_180x180.jpg") center / cover',
                photos: [],

                //slim
                croppers: [],
                editPhotoId: null,
                slimOptions: this.defaultSlimOptions(),
                preview_photo: [],
                date: 'today',
                manualLocationSet: false,
                customToolbar: [
                  ["bold", "italic", "underline"],
                  [{ list: "ordered" }, { list: "bullet" }]
                ],
                descriptionMaxLength: 3000
            }
        },
		    props: ['eventId'],
        components: {
            Map,
            DatePickerComponent,
            PhotoGallery,
            VideoGallery,
            PhotoSlider,
            VideoSlider,
            TimePickerComponent,
            VueEditor
        },
        computed: {
          mapHeight() {
              return this.type === 'guide' ? 'height: 265px' : '450px';
          },
          mapOptions() {
              return {
                scrollWheelZoom: false,
                dragging: true,
                touchZoom: true
              }
          },
          eventOriginal(){
              return this.$store.getters.getEvent(this.eventId)
          },
          textAreaDescription() {
            return (this.type === 'fun' || this.type === 'bang') ? this.trans('event_description') : 'What do you want to do?';
          },
          activePhotosCount() {
            return this.selectedPhotos.length;
          },
          activeVideosCount() {
            return this.selectedVideos.length;
          },
          leftPublicPhotosCount() {
            const maxAmount = app.maxPublicPicturesAmount;
            const publicPhotos = this.selectedPhotos;
            return (publicPhotos.length >= maxAmount) ? 0 : (maxAmount-publicPhotos.length);
          },
          leftPublicVideosCount() {
            const maxAmount = app.maxPublicVideosAmount;
            const publicVideos = this.selectedVideos;
            return (publicVideos.length >= maxAmount) ? 0 : (maxAmount-publicVideos.length);
          },
          titleRequired() {
            return this.type !== 'bang';
          }
        },
        mounted() {
            console.log('[Edit Event mobile] Mounted', {
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

            if (this.type === 'guide') {
              this.mapVisible = true;
            }

            this.is_private = this.event.is_private ? 1 : 0;
        },
        methods: {
            setSelectedPhotos(photos) {
              for (let key in photos) {
                this.selectedPhotos.push(photos[key])
                let index = _.findIndex(this.deletedPhotos, (e) => {
                  return e === photos[key].id;
                })
                if (index !== -1) {
                  this.deletedPhotos.splice(index, 1);
                }
              }
            },
            setVideos(videos) {
              for (let key in videos) {
                this.selectedVideos.push(videos[key])
                let index = _.findIndex(this.deletedVideos, (e) => {
                    return e === videos[key].id
                })
                if (index !== -1) {
                  this.deletedVideos.splice(index, 1);
                }
              }
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
                                self.selectedPhotos.push(photo);
                            }
                        });
                    } else if(key === 'videos') {
                        data[key].forEach(function (video) {
                            self.selectedVideos.push(video);
                        });
                    } else {
                        if (key === "event_date") {
                            this.date = "custom";
                        }

                        if (key === 'address') {
                          this.addressShow = data[key];
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
            this.manualLocationSet = true;
          },
          setPhotos(data) {
	            this.photos.push(data);
              this.selectedPhotos.push(data.id);
          },
          setDeletePhotos(data, index) {
            this.deletedPhotos.push(data.id);
            this.selectedPhotos.splice(index, 1);
            data.active = false;

            if (this.$refs.PhotoGallery.photos !== null) {
              this.$refs.PhotoGallery.photos.push(data)
            } else {
              this.$refs.PhotoGallery.photos = []
            }
          },
          setDeleteVideos(data, index) {
            this.deletedVideos.push(data.id);
            this.selectedVideos.splice(index, 1);
            data.active = false;

            if (this.$refs.VideoGallery.videos !== null) {
              this.$refs.VideoGallery.videos.push(data);
            } else {
              this.$refs.VideoGallery.videos = [];
            }
          },
          checkDeletePhoto(photoId) {
              const filterId = this.deletedPhotos.filter((filter) => {
                return filter === photoId;
              });

              if (filterId) {
                this.removeByValue(this.deletedPhotos, photoId);
              }
          },
          addTag() {
            if (this.newTag && !this.tags.includes(this.newTag)) {
              let arrayTag = {name: this.newTag};
              this.tags.push(arrayTag)
            }

            this.newTag = ''
          },
          deleteTag(tag) {
              const index = this.tags.indexOf(tag);
              this.tags.splice(index, 1);
          },
          clearAvatar(slot) {
            let photo = this.preview_photo;

            app.$emit('avatar-preloader', { slot, value: true })
            this.defaultEventPhoto = 'url("/assets/img/default_180x180.jpg") center / cover';
            this.preview_photo = [];
            this.deletedPhotos.push(photo.id);

            app.$emit('avatar-preloader', { slot, value: false })
          },
          setType(type) {
            if (this.type !== type) {
              return;
            }
            this.type = type
            if (type !== 'fun') {
              this.chemsfriendly = false
            }
          },
          openGallery(slot, defaultImg = false) {
            app.$emit('show-photo-event-gallery', slot, defaultImg)
          },
          chooseMap() {
            this.mapVisible = true
            this.manualLocationSet = true
            this.addressError = null
            this.$nextTick(() => {
              document.getElementById('map-box').scrollIntoView();
            })
          },
          async chooseGps() {
            this.mapVisible = false
            this.manualLocationSet = false
            this.addressError = null
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
              'is_profile_linked', 'is_private', 'time', 'location', 'locality', 'state',
              'country', 'country_code', 'address', 'lat', 'lng', 'address_type',
              'tags', 'preview_photo', 'deletedPhotos', 'deletedVideos', 'venue', 'website', 'contact', 'note', 'name'
            ])

            event.photos = this.selectedPhotos.map((photo) => {
                return photo.id
            })

            event.videos = this.selectedVideos.map((video) => {
                return video.id
            })

            if (this.type === 'fun') {
              event.chemsfriendly = event.chemsfriendly ? 1 : 0
            }

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
