<template>
	<div>
		<form data-abide2 novalidate data-vv-scope="form1">
			<div class="center">
        <div class="name-edit">
          <label>
            <input type="text" v-bind:placeholder="trans('clubs.title')" maxlength="50" :required="true" v-bind:data-vv-as="trans('clubs.title')" v-model="title" v-validate="{required: true}" :class="{'is-invalid-input': errors.has('form1.title')}" name="title">

            <span class="form-error" :class="{'is-visible': errors.has('form1.title')}">
              {{ errors.first('form1.title') }}
            </span>
          </label>
        </div>

        <div class="box">
          <div id="club-main-pic" class="profile-main-pic" @click.self="openGallery('clear', true)">
            <div class="img" v-if="!defaultClubPhoto.match(/default_180x180/)"
              :style="{'background': defaultClubPhoto}">
            </div>
            <div v-if="!defaultClubPhoto.match(/default_180x180/)"
              @click.stop="clearAvatar('clear')" class="close">
            </div>
          </div>
        </div>

        <PhotoGallery ref="PhotoGallery"/>

        <div class="box" :style="{'border-bottom': '1px solid #004646'}">
          <div class="row">
            <div class="headline">Description</div>
              <textarea
                :style="{'padding-bottom': textarea.height ? 30 + 'px' : 0}"
                v-model="description"
                v-bind:placeholder="trans('clubs.description_placeholder')"
                :rows="3"
                ref="textAreaDescription"
                maxlength="500"
                class="form-control no-swiping description"
                @input="resizeTextArea"
                @blur="unfocusTextArea"
                @key.up="() => {setTimeout(resizeTextArea, 100)}"
                v-bind:data-vv-as="trans('clubs.description_placeholder')"
                v-validate="'required'"
                required
                :class="{'is-invalid-input': errors.has('form1.description')}"
                name="description"
              ></textarea>
            <span class="form-error" :class="{'is-visible': errors.has('form1.description')}">
                Characters limit exceeded
            </span>
          </div>
        </div>

        <div class="box">
          <div class="website" v-if="userIsPro">
            <span class="link-icon"></span>
            <label>
              <input type="text" placeholder="web address" v-bind:data-vv-as="trans('clubs.website')" v-model="website" v-validate="{max: 750}" :class="{'is-invalid-input': errors.has('form1.website')}" name="website">
              <span class="form-error" :class="{'is-visible': errors.has('form1.website')}">
                  {{ errors.first('form1.website') }}
                </span>
            </label>
          </div>
        </div>

        <div class="box" :style="{'border-bottom': '1px solid #004646', 'padding-bottom': '30px'}">
          <div class="row">
            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.5 10H21.25V7.5C21.25 4.05 18.45 1.25 15 1.25C11.55 1.25 8.75 4.05 8.75 7.5V10H7.5C6.125 10 5 11.125 5 12.5V25C5 26.375 6.125 27.5 7.5 27.5H22.5C23.875 27.5 25 26.375 25 25V12.5C25 11.125 23.875 10 22.5 10ZM15 21.25C13.625 21.25 12.5 20.125 12.5 18.75C12.5 17.375 13.625 16.25 15 16.25C16.375 16.25 17.5 17.375 17.5 18.75C17.5 20.125 16.375 21.25 15 21.25ZM11.25 10V7.5C11.25 5.425 12.925 3.75 15 3.75C17.075 3.75 18.75 5.425 18.75 7.5V10H11.25Z" fill="#2F7570"/>
            </svg>
            <div class="title private-switch">
              {{ trans('clubs.invitation_only_club') }}
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

        <div class="box" :style="{'margin-bottom': '20px'}">
          <div class="row">
            <div class="title">{{ trans('location') }}</div>
            <div class="options" :style="{'margin-top': '8px'}"></div>
            <div class="detail location notranslate" v-if="(addressShow || addressError)">{{ addressShow }}
              <span class="form-error" :class="{'is-visible': addressError}">
                {{ addressError }}
              </span>
            </div>
          </div>
        </div>

        <div id="map-box" class="address" :class="{'show': true, 'no-address': !addressShow}">
          <div class="input-wrapper notranslate">
            <label>
              <input
                  class="form-control"
                  type="text"
                  name="address"
                  :placeholder="trans('clubs.city')"
                  :data-vv-as="trans('clubs.city')"
                  v-model="address"
                  @click="mapVisible = true"
                  v-validate="'required'"
                  :class="{'is-invalid-input': errors.has('form1.address')}"
                  @input="handleAddressModification"
                  required
              />
              <span class="form-error" :class="{'is-visible': errors.has('form1.address')}">
                {{ errors.first('form1.address') }}
              </span>
            </label>
          </div>
          <div v-show="mapVisible" class="map-box" :style="{'height': '450px'}">
            <Map
                :lat="lat"
                :lng="lng"
                :zoom="15"
                :clickable="true"
                :draggable="true"
                :options="mapOptions"
                :dragEnd="handleMapMarkerDrag"/>
            <div class="float-button">
              <button type="button" class="btn set-location" @click="setLocation">{{ trans('set_location') }}</button>
            </div>
          </div>
        </div>
        <button class="btn publish" id="button6" type="button" @click="submit">{{ trans('clubs.publish') }}</button>
			</div>
		</form>
	</div>
</template>

<script>

	import PhotoGallery from '@clubs/views/widgets/create/media/PhotoGallery.vue'
	import _ from "lodash";
	import Map from '@buddy/views/widgets/Map.vue';
	import { mapActions, mapState } from "vuex";
	import clubsModule from '@clubs/module/store/type'
  import moment from "moment";

  import { ErrorBag } from 'vee-validate';

	export default {
			mixins: [
					require('@general/lib/mixin').default,
					require('@general/lib/mixin-clubs').default
			],
			props: ['vars'],
			data() {
					return {
						...this.vars,
            manualLocationSet: false,
            timer: null,
            is_private:0,
					}
			},
			components: {
					PhotoGallery,
					Map
			},
			computed: {
          ...mapState({
              userIsPro: 'userIsPro',
          }),
          mapOptions() {
              return {
                scrollWheelZoom: false,
                dragging: true,
                touchZoom: true
              }
          }
			},
      watch: {
        type(){
          if (app.isDesktop) {
            this.$parent.$parent.$refs.vueCustomScrollbar.$forceUpdate()
          }
        }
      },
      mounted() {
          this.$store.dispatch('loadCurrentUserInfo');
          if (this.type === 'guide') {
            this.mapVisible = true;
          }
      },
      methods: {
					...mapActions({
						setClub: clubsModule.actions.setClub,
						saveClub: clubsModule.actions.clubs.submit
					}),
          defaultEventBackground(data) {
            console.log('defaultEventBackground', data)
						if (data) {
						  let photo = data[0];
							this.defaultClubPhoto = `url(${photo.photo_small}) center / cover`
							photo.is_default = 'yes';
							this.preview_photo = photo;
						}
					},
					async clearAvatar(slot) {
            let photo = this.preview_photo;

            app.$emit('avatar-preloader', { slot, value: true })
            await this.makePhotoVisibleTo(photo.id, 'private')
            this.defaultClubPhoto = 'url("/assets/img/default_180x180.jpg") center / cover';
            this.preview_photo = [];
            await this.$store.dispatch('loadCurrentUserInfo')
            app.$emit('avatar-preloader', { slot, value: false })
					},
          setLocation() {
					    this.mapVisible = false;
					    this.manualLocationSet = true;
          },
					openGallery(slot, defaultImg = false) {
							app.$emit('show-photo-event-gallery', slot, defaultImg)
					},
          unfocusTextArea() {
            if (this.description === '') {
              this.resetTextAreaSize();
            }
          },
          resetTextAreaSize(){
            this.textarea.rows   = 3
            this.textarea.height = 45
          },
          resizeTextArea() {

            let rowsCount = Math.ceil((this.$refs.textAreaDescription.scrollHeight - 12 * 2) / 21)

            this.textarea.rows = rowsCount + 1 > 6 ? 6 : rowsCount

            if (!this.isMobile) {
              this.textarea.height = this.textarea.rows * 21 + 12 * 2
            } else {
              if (rowsCount === 1) this.textarea.height = null
              else if (rowsCount === 2) this.textarea.height = 59
              else if (rowsCount === 3) this.textarea.height = 80
              else if (rowsCount === 4) this.textarea.height = 101
              else if (rowsCount === 5) this.textarea.height = 122
              else if (rowsCount >= 6) this.textarea.height = 143
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
					async submit() {

            this.$validator.validate()

						let club = _.pick(this, [
							'title', 'description', 'location', 'locality', 'state',
							'country', 'country_code', 'address', 'lat', 'lng', 'preview_photo', 'website', 'is_private',
						])

            // if (this.deletedPhotos.length > 0) {
            //   event.deleted_photos = this.deletedPhotos;
            // }

            // event.photos = this.selectedPhotos.map((photo) => {
            //   return photo.id
            // })

						// event.videos = this.selectedVideos.map((video) => {
            //   return video.id
            // })

						this.showLoadingButton('#button6')

            this.saveClub(club)
                .then(() => {
                  this.restoreLoadingButton('#button6')
                }).catch(() => {
              this.restoreLoadingButton('#button6')
            })
					},
			},
      deactivated() {
        const data = this.$parent.defaultData();

        for (let key in data) {
          this[key] = data[key];
        }

        this.$validator.pause()
        this.$nextTick(() => {
          this.$validator.errors.clear()
          this.$validator.fields.items.forEach(field => field.reset())
          this.$validator.fields.items.forEach(field => this.errors.remove(field))
          this.$validator.resume()
        })
      }
  }
</script>

<style scoped>
  .datepicker-active {
    background: linear-gradient(180deg, #00F000 0%, #00B400 100%) !important;
    color: black !important;
  }

  .no-address {
      padding-top: 20px;
  }

</style>
<style>
.private-switch {
  color:white;
  font-size:16px !important;
}

.disable-overflow {
  overflow-y: hidden !important;
}
</style>
