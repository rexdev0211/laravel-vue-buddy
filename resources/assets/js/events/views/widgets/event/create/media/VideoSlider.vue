<template>
  <div class="row profile-videos">
    <div class="item vid"
         :class="{'added': video.id}"
         v-for="(video, index) in items"
         @click="!video.id ? showVideoGallery() : false">
      <div v-if="video.id"
           :id="`profile-video-${video.id}`"
           class="img"
           :style="{'background': `url(${video.thumb_small}) center / cover`}">
      </div>
      <div v-else class="img"></div>
      <div class="close" v-if="video.id" ::title="trans('delete_video')"
           @click.prevent="makeVideoPrivate(video, index, $event)">
      </div>
    </div>
  </div>
</template>

<script>
import discoverModule from '@discover/module/store/type'
import {mapState, mapActions, mapGetters} from 'vuex';

export default {
  mixins: [require('@general/lib/mixin').default],
  props: ['videos'],
  methods: {
    ...mapActions({
      requirementsAlertShow: 'requirementsAlertShow',
    }),
    showVideoGallery(){
      if (!this.userIsPro && !this.$parent.leftPublicVideosCount === 0) {
        this.$store.dispatch('requirementsAlertShow', 'media')
      } else {
        app.$emit('show-video-gallery')
      }
    },
    chooseVideo() {
      this.$refs.video.click();
    },
    deleteVideo(event) {
      let position = this.videos.indexOf(this.video);
      const videoId = this.video.id

      let callback = () => {
        return axios.get(`/api/videos/delete/${videoId}`)
            .then(() => {
              this.videos.splice(position, 1);
              this.$store.dispatch(discoverModule.actions.users.update, {
                userId: auth.getUserId(),
                fields: {
                  has_videos: !!this.publicVideos.length
                }
              })

              this.removeVideoFromEvents(videoId)
              this.goToVideos();
            });
      };

      let self = this
      this.$store.dispatch('showDialog', {
        mode: 'confirm',
        message: this.trans('sure_delete_video'),
        callback: () => { self.runLoadingFunction(event.target, callback); }
      })
    },
    makeVideoPrivate(video, index, event) {
      this.$parent.setDeleteVideos(video, index);
    },
  },
  computed: {
    ...mapState({
      userIsPro: 'userIsPro',
    }),
    ...mapGetters([
      'leftPublicVideosCount',
      'publicVideos',
      'publicVideosCount',
    ]),
    items(){
      let items = []
      let placeholdersArray = []
      let placeholdersArrayLength = 1

      if (this.videos?.length > 0) {
        let publicVideosLength = this.videos.length;
        placeholdersArrayLength = publicVideosLength < 5 ? 5 - publicVideosLength : 1;
        placeholdersArray = Array.from({length: placeholdersArrayLength}, (_, i) => {return {}})

        items.push(...this.videos, ...placeholdersArray)
        return items;
      } else {
        if (!this.userIsPro) {
          placeholdersArrayLength = this.$parent.leftPublicVideosCount + 1
        } else {
          placeholdersArrayLength = this.$parent.activeVideosCount < 5 ? 5 - this.$parent.activeVideosCount : 1
        }
        placeholdersArray = Array.from({length: placeholdersArrayLength}, (_, i) => {return {}})
        items.push(...this.videos, ...placeholdersArray)
        return items
      }
    },
  }
}
</script>
