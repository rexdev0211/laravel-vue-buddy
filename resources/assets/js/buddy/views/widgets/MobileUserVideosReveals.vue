<template>
  <div class="w-root" v-if="viewVideoRevealVisible">
    <div class="w-views">
      <div class="secondary-menu media-watching">

        <div class="secondary-menu-header">
          <i class="back" @click="closeRevealEditVideo"></i>
          <div class="title" v-if="!dataLoading">{{ trans('pos_of_total', {position: videoPosition, total: videos.length}) }}</div>
        </div>

        <div class="secondary-menu-body">

          <div class="w-swiper swiper-container" :id="'swiper-'+userId">
            <!-- Additional required wrapper -->
            <div class="swiper-wrapper" @click="zoomSwiper()">
              <!-- Slides -->
              <div class="swiper-slide" v-if="!dataLoading" v-for="(video, index) in videos" :key="index">
                <div class="swiper-zoom-container b-profile__photo media">
                  <VideoPlayer :poster="video.thumb_orig" :videoSource="video.video_url"></VideoPlayer>
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
import VideoPlayer from '@buddy/views/widgets/VideoPlayer.vue';

export default {
  mixins: [require('@general/lib/mixin').default],
  props: ['userToken', 'videos'],
  data() {
    return {
      viewVideoRevealVisible:false,

      pid: null,
      delay: 300,
      clicks: 0,
      timer: null,
      isZoomed: false,
    }
  },
  components: {
    VideoPlayer,
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
    video() {
      return this.videos && this.videos.find(el => el.id == this.pid);
    },
    videoPosition() {
      return this.videos && this.videos.indexOf(this.video) + 1;
    }
  },
  methods: {
    showEditVideoReveal(viewVideoId) {
      this.viewVideoRevealVisible = true;
      this.pid = viewVideoId;

      //to make left/right keys work
      setTimeout(() => {
        $('.secondary-menu.media-watching').focus();
      }, 0)

      console.log('VIDEOS:');
      console.log(this.videos);

      console.log('USER TOKEN:');
      console.log(this.userToken);

      console.log('ONE VIDEO:');
      console.log(this.video);
      console.log(this.videoPosition);

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
    closeRevealEditVideo() {
      this.viewVideoRevealVisible = false;
    },

    onVideoChanged(newIndex, zoomed = false) {
      this.pid = this.videos[newIndex].id;
      this.isZoomed = zoomed;
    },
    onVideoZoomed(zoomed) {
      this.isZoomed = zoomed;
    },
    loadSwiperScript() {
      this.loadSwiperPopup('swiper-'+this.userId, this.videoPosition - 1, this.onVideoChanged);
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
            this.swiperZoomOut(this.onVideoZoomed)
          } else if (!this.isZoomed) {
            this.swiperZoomIn(this.onVideoZoomed)
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
    app.$on('show-view-video-reveal', this.showEditVideoReveal);
  },
  beforeDestroy() {
    app.$off('show-view-video-reveal');
  },
}
</script>
<style scoped>
.media-watching {
  height: 100vh;
}
</style>