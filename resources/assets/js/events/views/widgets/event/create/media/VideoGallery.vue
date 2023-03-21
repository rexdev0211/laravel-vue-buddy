<template>
  <transition :name="'slide-in-bottom'" mode="out-in">
    <div class="add-pics-box video"
         v-if="visible"
         @click.self="hide"
         tabindex="0">
      <div class="wrapper">
        <div class="inner">
          <div class="close" @click="hide"></div>
          <vue-custom-scrollbar v-if="isDesktop" class="pics-catalog">
            <div class="pic upload-photo"
                 id="addVideoButton"
                 @click="chooseVideo">
              <input type="file" id="newVideoUpload" class="show-for-sr" name="video" accept="video/*" ref="video" v-on:change="uploadVideo($event)">
            </div>
            <div v-if="!videos || !videos.length" class="add-media-hover">
              <span>{{ trans('add_video') }}</span>
            </div>
            <div class="pic"
                 v-for="(video, index) in videos"
                 @click="selectMedia(video.id, index, video.active, $event)"
                 :class="{'selected': video.active}"
                 :style="{'background': !video.id ? 'url(/assets/img/preloader.svg) center no-repeat' : 'none'}">
              <div class="img"
                   v-if="!!video.id"
                   :id="`profile-video-${video.id}`"
                   :style="{'background': `url(${video.thumb_small}) center / cover`}">
              </div>
            </div>
          </vue-custom-scrollbar>
          <div v-else class="pics-catalog">
            <div class="pic upload-photo"
                 id="addVideoButton"
                 @click="chooseVideo">
              <input type="file" id="newVideoUpload" class="show-for-sr" name="video" accept="video/*" ref="video" v-on:change="uploadVideo($event)">
            </div>
            <div v-if="!videos || !videos.length" class="add-media-hover">
              <span>{{ trans('add_video') }}</span>
            </div>
            <div class="pic"
                 v-for="(video, index) in videos"
                 @click="selectMedia(video.id, index, video.active, $event)"
                 :class="{'selected': video.active}"
                 :style="{'background': !video.id ? 'url(/assets/img/preloader.svg) center no-repeat' : 'none'}">
              <div class="img"
                   v-if="!!video.id"
                   :id="`profile-video-${video.id}`"
                   :style="{'background': `url(${video.thumb_small}) center / cover`}">
              </div>
            </div>
          </div>
          <div class="float-button">
            <button class="btn" type="button" @click="submitVideos($event)">{{ trans('ok') }}</button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import {mapGetters, mapActions, mapState} from 'vuex';

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
      selectedVideos: [],
      videosCount: 0,
      videos: null,
    }
  },
  mounted() {
    app.$on('show-video-gallery', this.show);
  },
  beforeDestroy() {
    app.$off('show-video-gallery');
  },
  methods: {
    ...mapActions([
      'requirementsAlertShow'
    ]),
    chooseVideo() {
      this.$refs.video.click();
    },
    show(){
      app.$emit('show-scroll', false);

      this.visible = true

      let videos = this.profileVideos;
      let parentVideos = this.$parent.selectedVideos;

      if (parentVideos.length > 0) {
          for (let index in parentVideos) {
            let videoIndex = _.findIndex(videos, (video) => {
                return video.id === parentVideos[index].id;
            })

            if (videoIndex !== -1) {
              videos.splice(videoIndex, 1);
            }
          }
      }

      this.videos = videos;
    },
    hide() {
      app.$emit('show-scroll', true);

      this.videosCount = 0;
      this.selectedVideos = [];

      if (this.videos) {
        this.videos.map(video => {
          video.active = false;
        });
      }

      this.visible = false
    },
    submitVideos() {
      if (this.selectedVideos.length === 0) {
        this.hide();
      }

      this.$parent.setVideos(this.selectedVideos);

      this.videosCount = 0;
      this.selectedVideos = [];
      this.hide()
    },
    selectMedia(id, index, active, event) {
      let videosCount = this.selectedVideos.length + this.$parent.activeVideosCount;

      if (!this.userIsPro && videosCount === 4 && !active) {
        this.$store.dispatch('requirementsAlertShow', 'media')
        return;
      }

      if (!id) {
        return;
      }

      let video = this.videos[index];
      let videos = this.videos;

      video.active = !video.active;

      if (video.active) {
        this.selectedVideos.push(video);
        this.videosCount++;
      } else {
        let index = _.findIndex(this.selectedVideos, (e) => {
            return e.id === video.id
        })
        this.selectedVideos.splice(index, 1);
        this.videosCount--;
      }

      this.$set(videos, index, video);
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
    makeVideoPublic(video, event) {
      if (!this.userIsPro && !this.leftPublicVideosCount) {
        this.requirementsAlertShow('media')
      } else {
        this.makeVideoVisibleTo(video, 'public', event)
        this.hide()
      }
    }
  },
  computed: {
    ...mapGetters({
      leftPublicVideosCount: 'leftPublicVideosCount',
      publicVideosCount: 'publicVideosCount',
      privateVideos: 'privateVideos'
    }),
    ...mapState({
      profileVideos: 'profileVideos'
    })
  },
}
</script>