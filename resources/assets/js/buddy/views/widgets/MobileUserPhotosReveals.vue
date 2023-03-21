<template>
  <div class="w-root" v-if="viewPhotoRevealVisible">
    <div class="w-views">
      <div class="secondary-menu media-watching">

        <div class="secondary-menu-header">
          <i class="back" @click="closeRevealEditPhoto"></i>
          <div class="title" v-if="!dataLoading">{{ trans('pos_of_total', {position: photoPosition, total: photos.length}) }}</div>
        </div>

        <div class="secondary-menu-body">

          <div class="w-swiper swiper-container" :id="'swiper-'+userId">
            <!-- Additional required wrapper -->
            <div class="swiper-wrapper" @click="zoomSwiper()">
              <!-- Slides -->
              <div class="swiper-slide" v-if="!dataLoading" v-for="(photo, index) in photos" :key="index">
                <div class="swiper-zoom-container b-profile__photo media">
                  <div class="swiper-zoom-target img" :style="{'background-image': 'url(' + photo.photo_orig + ')'}"></div>
                </div>
              </div>
            </div>
          </div><!--swiper-container-->

        </div><!--secondary-menu-body-->
      </div><!--secondary-menu-->
    </div><!--w-views-->
  </div><!--w-root-->
</template>

<script>
import {mapState} from 'vuex';

export default {
  mixins: [require('@general/lib/mixin').default],
  props: ['userToken', 'photos'],
  data() {
    return {
      viewPhotoRevealVisible:false,

      pid: null,
      delay: 300,
      clicks: 0,
      timer: null,
      isZoomed: false,
    }
  },
  computed: {
    dataLoading() {
      return !this.user;
    },
    user(){
      return this.$store.getters.getUser(this.userToken)
    },
    userId(){
      return this.user ? this.user.id : null
    },
    photo() {
      return this.photos && this.photos.find(el => el.id == this.pid);
    },
    photoPosition() {
      return this.photos && this.photos.indexOf(this.photo) + 1;
    }
  },
  methods: {
    showEditPhotoReveal(viewPhotoId) {
      this.viewPhotoRevealVisible = true;
      this.pid = viewPhotoId;

      //to make left/right keys work
      setTimeout(() => {
        $('.secondary-menu.media-watching').focus();
      }, 0)

      console.log('PHOTOS:');
      console.log(this.photos);

      console.log('USER TOKEN:');
      console.log(this.userToken);

      console.log('ONE PHOTO:');
      console.log(this.photo);
      console.log(this.photoPosition);

      console.log('USER ID:');
      console.log(this.userId);

      // LAUNCH SWIPER

      if (!!this.userId && !this.user){
        this.$store.dispatch('loadUserInfo', this.userId)
      }

      this.$nextTick(function(){
        if (!this.dataLoading) {
          this.loadSwiperScript();
        }
      })
    },
    closeRevealEditPhoto() {
      this.viewPhotoRevealVisible = false;
    },
    onPhotoChanged(newIndex, zoomed = false) {
      this.pid = this.photos[newIndex].id;
      this.isZoomed = zoomed;
    },
    onPhotoZoomed(zoomed) {
      this.isZoomed = zoomed;
    },
    loadSwiperScript() {
      this.loadSwiperPopup('swiper-'+this.userId, this.photoPosition - 1, this.onPhotoChanged);
    },
    zoomSwiper() {
      if (this.isIos()) {
        this.clicks++
        if (this.clicks === 1) {
          this.timer = setTimeout(() => {
            this.clicks = 0
          }, this.delay)
        } else {
          if (this.isZoomed) {
            this.swiperZoomOut(this.onPhotoZoomed)
          } else if (!this.isZoomed) {
            this.swiperZoomIn(this.onPhotoZoomed)
          }
        }
      }
    }
  },
  watch: {
    dataLoading: function (loading) {
      if (!loading) {
        this.$nextTick(function(){
          this.loadSwiperScript();
        })
      }
    }
  },
  mounted() {
    app.$on('show-view-photo-reveal', this.showEditPhotoReveal);
  },
  beforeDestroy() {
    app.$off('show-view-photo-reveal');
  },
}
</script>
<style scoped>
.media-watching {
  height: 100vh;
}
</style>