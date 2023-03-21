<template>
    <transition :name="'slide-in-bottom'" mode="out-in">
      <div class="add-pics-box"
           v-if="visible"
           @click.self="hide"
           tabindex="0">
        <div class="wrapper">
          <div class="inner">
            <div class="close" @click="hide"></div>
            <vue-custom-scrollbar v-if="isDesktop" class="pics-catalog">
              <div class="pic upload-photo"
                   id="addPhotoButton"
                   @click="choosePhoto">
                <input type="file" id="newPhotoUpload" class="show-for-sr" name="photo" accept="image/*" ref="photo" v-on:change="uploadProfilePhoto($event, true)" :placeholder="trans('reg.add_photo')">
              </div>
              <div v-if="!photos.length" class="add-media-hover">
                <span>{{ trans('reg.add_photo') }}</span>
              </div>
              <div class="pic"
                   v-for="(photo, index) in photos"
                   :class="{'selected': photo.active}"
                   @click="selectMedia(photo.id, index, photo.active, $event)">
                <div class="img"
                     :id="`profile-photo-${photo.id}`"
                     :style="{'background': `url(${photo.photo_small}) center / cover`}">
                </div>
              </div>
            </vue-custom-scrollbar>
            <div v-else class="pics-catalog">
              <div class="pic upload-photo"
                   id="addPhotoButton"
                   @click="choosePhoto">
                <input type="file" id="newPhotoUpload" class="show-for-sr" name="photo" accept="image/*" ref="photo" v-on:change="uploadProfilePhoto($event, true)" :placeholder="trans('reg.add_photo')">
              </div>
              <div v-if="!photos.length" class="add-media-hover">
                <span>{{ trans('reg.add_photo') }}</span>
              </div>
              <div class="pic"
                   v-for="(photo, index) in photos"
                   :class="{'selected': photo.active}"
                   @click="selectMedia(photo.id, index, photo.active, $event)">
                <div class="img"
                     :id="`profile-photo-${photo.id}`"
                     :style="{'background': `url(${photo.photo_small}) center / cover`}">
                </div>
              </div>
            </div>
            <div class="float-button">
              <button class="btn" type="button" @click="submitPhotos($event)">{{ trans('ok') }}</button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </template>
  
  <script>
  import {mapState, mapGetters, mapActions} from 'vuex';
  
  // custom scrollbar
  import vueCustomScrollbar from 'vue-custom-scrollbar'
  import "vue-custom-scrollbar/dist/vueScrollbar.css"
  
  
  export default {
    mixins: [
      require('@general/lib/mixin').default
    ],
    components: {
      vueCustomScrollbar
    },
    data() {
      return {
        visible: false,
        slot: null,
        isSelected: false,
        selectedPhotos: [],
        photos: null,
        photosCount: 0
      }
    },
    mounted() {
      app.$on('show-photo-event-gallery', this.show);
    },
    beforeDestroy() {
      app.$off('show-photo-event-gallery');
    },
    methods: {
      ...mapActions([
        'requirementsAlertShow',
      ]),
      choosePhoto() {
        this.$refs.photo.click();
      },
      show(slot){
        app.$emit('show-scroll', false);
  
        this.visible = true
        this.slot = slot
  
        let photos = this.profilePhotos
        let parentPhotos = this.$parent.selectedPhotos;
  
        if (parentPhotos.length > 0) {
          for (let index in parentPhotos) {
              let photoIndex = _.findIndex(photos, (photo) => {
                  return photo.id === parentPhotos[index].id;
              })
  
              if (photoIndex !== -1) {
                photos.splice(photoIndex, 1);
              }
          }
        }
  
        this.photos = photos;
  
        if (this.visible) {
          this.photosCount = this.publicPhotosCount;
        }
      },
      hide() {
        app.$emit('show-scroll', true);
  
        this.photosCount = 0;
        this.selectedPhotos = [];
  
        this.photos.map(photo => {
          photo.active = false;
        });
  
        this.visible = false
        this.slot = null
      },
      selectMedia(id, index, active) {
        if (this.slot) {
          this.selectMainEventPhoto(index, active);
        } else {
          this.selectEventPhotos(index, active);
        }
      },
      selectMainEventPhoto(index, active) {
        let photos = this.photos;
        let photo = this.photos[index];
        let routePath = this.$router.currentRoute.path;
  
        if (this.selectedPhotos.length === 1 && !active) {
          const deletePhotoId = this.selectedPhotos[0];
  
          const changedPhoto = photos.filter((photo, index) => {
            if (photo.id === deletePhotoId.id) {
              photo.index = index;
              photo.active = false;
  
              return photo;
            }
          });
  
          this.$set(photos, changedPhoto[0].index, changedPhoto[0]);
  
          this.selectedPhotos.splice(0, 1);
        }
  
        photo.active = !photo.active;
  
        if (photo.active) {
          this.selectedPhotos.push(photo);
  
          if (routePath !== '/create-club' || app.isDesktop && routePath !== '/clubs') {
            this.$parent.checkDeletePhoto(photo.id);
          }
        } else {
            let index = _.findIndex((e) => {
                return e.id === photo.id;
            })
  
            if (index !== -1) {
              this.selectedPhotos.splice(index, 1);
            }
        }
  
        this.$set(photos, index, photo);
      },
      selectEventPhotos(index, active) {
        let photosCount = this.selectedPhotos.length + this.$parent.activePhotosCount;
  
        if (!this.userIsPro && photosCount === 4 && !active) {
          this.$store.dispatch('requirementsAlertShow', 'media');
          return;
        }
  
        let routePath = this.$router.currentRoute.path;
        let photo = this.photos[index];
        let photos = this.photos;
  
        photo.active = !photo.active;
  
        if (photo.active) {
          this.selectedPhotos.push(photo);
          this.photosCount++;
  
          if (routePath !== '/create-club' || routePath !== '/clubs') {
            this.$parent.checkDeletePhoto(photo.id);
          }
        } else {
          let index = _.findIndex(this.selectedPhotos, (e) => {
              return e.id === photo.id;
          })
  
          if (index !== -1) {
            this.selectedPhotos.splice(index, 1);
            this.photosCount--;
          }
        }
  
        this.$set(photos, index, photo);
      },
      submitPhotos() {
        if (this.slot) {
          this.$parent.defaultEventBackground(this.selectedPhotos);
        } else {
          this.$parent.setSelectedPhotos(this.selectedPhotos);
        }
  
        for (let key in this.selectedPhotos) {
          this.photos.splice(_.findIndex(this.photos, (e) => {
            return e.id === this.selectedPhotos[key].id;
          }), 1);
        }
  
        this.hide();
      },
    },
    computed: {
      ...mapState({
        userIsPro: 'userIsPro',
        profilePhotos: 'profilePhotos'
      }),
      ...mapGetters([
        'publicPhotosCount',
        'privatePhotos',
        'leftPublicPicturesCount'
      ]),
    }
  }
  </script>