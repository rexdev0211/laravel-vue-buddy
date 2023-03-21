<template>
  <div>
    <div class="wrap">
      <div class="pics-catalog">
        <div class="pic upload-video"
          v-if="!urlSharingMode"
          id="addVideoButton"
          @click="chooseVideo">
          <input type="file" id="newVideoUpload" class="show-for-sr" name="video" accept="video/*" ref="video" v-on:change="uploadVideo($event)">
        </div>
        <div class="pic" v-for="(video, index) in videos" @click="goToVideo(video.id); selectVideo(video.id, index, video.active)"
            :class="{
                'loading': !video.id,
                'selected': video.active
            }"
        >
          <div class="spinner" v-if="!video.id && !video.percentage"></div>
          <div class="video-progress" 
            :style="{'background': `conic-gradient(#00F000 ${video.percentage}%, #2F7570 ${video.percentage}%)`}"
            v-if="!video.id && video.percentage">
            <div class="video-progress-circle"></div>
          </div>
          <div class="img"
              :id="`profile-video-${video.id}`"
              :style="{'background': `url(${video.thumb_small}) center / cover`}"
              v-if="!!video.id">
          </div>
        </div>
        <div class="share_videos-button"
          v-if="!urlSharingMode && !haveSharingVideos"
          @click="activeSharingMode()"
          :title="trans('share_videos')"
        >
          <div class="icon icon-share_videos"></div>
        </div>
      </div>
    </div>
    <div class="sharing-footer" v-if="urlSharingMode && !haveSharingVideos">
      <div class="share-buttons">
        <button @click="disableSharingMode()" id="cancel" type="button" class="btn darker">
          {{ trans('cancel_share') }}
        </button>
        <button id="share" :disabled="selectedVideos.length === 0" type="button" class="btn" @click="shareVideo">
          {{ trans('share_now') }}
        </button>
      </div>
      <div class="share-text">
        {{ trans('tap_on_the_videos_you_want_to_share') }}
      </div>
    </div>
    <div class="sharing-menu" :class="{'opened': haveSharingVideos}">
      <div class="wrapper">
        <div class="inner">
          <div class="close" @click="closeUrlSharing"></div>
          <div class="sharing-block">
            <div class="title">{{ trans('this_is_your_sharing_code') }}:</div>
            <div class="sharing-url">
              <div class="btn" @click="copyUrl(sharingUrl)">
                {{ sharingUrl }}
              </div>
            </div>
            <div class="description" v-html="trans('share_your_selected_videos')"></div>
          </div>
          <div class="sharing-whatsapp">
            <div class="whatsapp-button">
              <a :href="'whatsapp://send?text='+trans('share_whatsapp_test')+' '+sharingUrl" data-action="share/whatsapp/share" style="text-decoration:none;">{{ trans('send_via_whatsapp') }}</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">

  .video-progress {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
  }

  .video-progress-circle {
    position: absolute;
    top: 5px;
    left: 5px;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(180deg, #004646 0%, #002828 100%);
  }

  @keyframes progress-1tucza {
    0% {
      clip-path: polygon(50% 50%,0 0,0    0,0    0   ,0    0   ,0    0   );
    }

    25% {
      clip-path: polygon(50% 50%,0 0,100% 0,100% 0   ,100% 0   ,100% 0   );
    }

    50% {
      clip-path: polygon(50% 50%,0 0,100% 0,100% 100%,100% 100%,100% 100%);
    }

    75% {
      clip-path: polygon(50% 50%,0 0,100% 0,100% 100%,0    100%,0    100%);
    }

    100% {
      clip-path: polygon(50% 50%,0 0,100% 0,100% 100%,0    100%,0    0   );
    }
  }

  .spinner {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 5px solid;
    border-color: #2f7570;
    border-right-color: #00f000;
    animation: spinner-d3wgkg 1.4s infinite linear;
  }

  @keyframes spinner-d3wgkg {
    to {
      transform: rotate(1turn);
    }
  }
</style>

<script>
  import {mapState, mapActions, mapGetters} from 'vuex';

  export default {
      mixins: [require('@general/lib/mixin').default],
      props: [
        'urlSharingModeParent',
        'haveSharingVideosParent',
        'selectedVideosParent'
      ],
  data() {
      return {
          urlSharingMode: typeof this.urlSharingModeParent != 'undefined' ? this.urlSharingModeParent : false,
          haveSharingVideos: typeof this.haveSharingVideosParent != 'undefined' ? this.haveSharingVideosParent : false,
          selectedVideos: typeof this.selectedVideosParent != 'undefined' ? this.selectedVideosParent : [],

      }
  },
  mounted () {
    app.$on('changeUrlSharingMode', this.disableSharingMode)
    this.videos.forEach(video => {
      if (video.status === 'processing' || video.status === 'accessible') {
        this.getVideoProcessPercentage(video.hash)
      }
    })
  },
  beforeDestroy() {
    this.disableSharingMode();
  },
  methods: {
        ...mapActions({
            requirementsAlertShow: 'requirementsAlertShow',
            shareVideos: 'shareVideos',
        }),
        async copyUrl(mytext) {
          try {
            await navigator.clipboard.writeText(mytext);
            this.showSuccessNotification(this.trans('copied_to_clipboard'));
          } catch($e) {
            alert('Cannot copy');
          }
        },
        closeUrlSharing() {
            this.haveSharingVideos = false;
            this.disableSharingMode();
        },
        activeSharingMode() {
            this.urlSharingMode = true
            this.$emit('toggleSharingMode', this.urlSharingMode)
        },
        disableSharingMode() {
          this.urlSharingMode = false
          this.selectedVideos = []
          this.removeActiveClass()
          this.$emit('toggleSharingMode', this.urlSharingMode)
        },
        removeActiveClass() {
          this.videos.map(function (video) {
            video.action = false;
            video.active = false;
          })
        },
        chooseVideo() {
            this.$refs.video.click();
        },
        selectVideo(videoId, index, videoActive) {
            if (!videoId || !this.urlSharingMode) {
              return;
            }

            let currentVideo = this.videos[index]
            let allVideos = this.videos

            currentVideo.active = !currentVideo.active

            if (currentVideo.active) {
                this.selectedVideos.push(videoId)
            } else {
                this.selectedVideos.splice(_.findIndex(this.selectedVideos, videoId), 1)
            }

            this.$set(allVideos, index, currentVideo)
        },
        shareVideo() {
            this.haveSharingVideos = true;
            this.removeActiveClass()
            this.shareVideos(this.selectedVideos);
        },
        goToVideo(videoId) {
            if (!videoId || this.urlSharingMode) {
                return
            }

            if (this.isMobile) {
                this.$router.push(`/profile/video/${videoId}`);
            } else {
                app.$emit('show-edit-video-reveal', videoId);
            }
        },
        makeVideoVisibleToLocal(video, state, event) {
            if (state === 'public' && !this.userIsPro && !this.leftPublicVideosCount) {
                this.requirementsAlertShow('media')
            } else {
                this.makeVideoVisibleTo(video, state, event);
            }
        },
    },
    computed: {
        dataLoading() {
            return this.$parent.dataLoading;
        },
        ...mapState({
            videos:        'profileVideos',
            userIsPro:     'userIsPro',
        }),
        ...mapGetters({
            sharingUrl: 'sharingUrl'
        }),
        leftPublicVideosCount() {
            return this.$store.getters.leftPublicVideosCount;
        },
        publicVideosCount() {
            return this.$store.getters.publicVideosCount;
        }
    }
  }
</script>
