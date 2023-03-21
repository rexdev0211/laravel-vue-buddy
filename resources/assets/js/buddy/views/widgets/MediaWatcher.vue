<template>
  <div
      ref="mediaWatcher"
      class="secondary-menu media-watching"
      v-if="visible"
      @click.self="close"
      @keyup.left="goPrev"
      @keyup.right="goNext"
      @keyup.esc="close"
      tabindex="0"
      :class="fixed?'fixed':''"
  >
      <div class="secondary-menu-header" style="position: sticky">
        <div class="title">{{ trans('pos_of_total', {position: itemPosition, total: items.length}) }}</div>
        <i v-if="canDelete" class="trash" @click="deleteItem"></i>
        <i class="back" @click="close"></i>
      </div>

      <div class="secondary-menu-body">
        <i v-if="fullscreen" class="back" @click="close"></i>
        <div class="next" @click="goNext"></div>
        <div class="prev" @click="goPrev"></div>
        <div class="media">
          <template v-if="type === 'photo'">
            <div class="img" v-if="item"
                 :style="{'background': `url(${item.photo_orig}) no-repeat center / contain`}">
            </div>
          </template>
          <template v-else-if="type === 'video'">
            <VideoPlayer :poster="item.thumb_orig" :videoSource="item.video_url"></VideoPlayer>
          </template>
        </div>
    </div>
  </div>
</template>

<script>
import VideoPlayer from "./VideoPlayer";
import discoverModule from "../../../discover/module/store/type";

export default {
  name: "MediaWatcher",
  props: ['items', 'type', 'canDelete','fixed', 'fullscreen'],
  mixins: [require('@general/lib/mixin').default],
  components: {
    VideoPlayer
  },
  data () {
    return {
      visible: false,
      itemId: 0,
    };
  },
  methods: {
    show(itemId) {
      this.visible = true;
      this.itemId = itemId;
      setTimeout(() => {
        this.$refs.mediaWatcher.focus({preventScroll:true});
      }, 0)
    },
    goNext() {
      const nextPos = (this.itemPosition >= this.items.length) ? 1 : this.itemPosition + 1;
      this.itemId = this.items[nextPos-1].id
    },
    goPrev() {
      const prevPos = (this.itemPosition <= 1) ? this.items.length : this.itemPosition- 1;
      this.itemId = this.items[prevPos-1].id
    },
    close() {
      this.visible = false;
    },
    deleteItem (event) {
      let position = this.items.indexOf(this.item);
      const itemId = this.item.id
      let callback;
      let transValue;
      if (this.type === 'photo') {
        transValue = 'sure_delete_photo'
        callback = () => {
          return axios.get(`/api/photos/delete/${itemId}`)
              .then(() => {
                this.items.splice(position, 1)
                this.removePhotoFromEvents(itemId)
                this.close()
              })
        };
      } else if (this.type === 'video') {
        transValue = 'sure_delete_video'
        callback = () => {
          return axios.get(`/api/videos/delete/${itemId}`)
              .then(() => {
                this.items.splice(position, 1);

                this.$store.dispatch(discoverModule.actions.users.update, {
                  userId: auth.getUserId(),
                  fields: {
                    has_videos: !!this.publicVideos.length
                  }
                })

                this.removeVideoFromEvents(itemId)
                this.close();
              });
        };
      } else {
        return;
      }
      let self = this
      this.$store.dispatch('showDialog', {
        mode: 'confirm',
        message: this.trans(transValue),
        callback: () => {
          self.runLoadingFunction(event.target, callback);
        }
      })
    }
  },
  computed: {
    item() {
      return this.items.find(e => e.id === this.itemId)
    },
    itemPosition() {
      return this.items.indexOf(this.item) + 1;
    },
    publicVideos() {
      return this.type === 'video'?this.$store.getters.publicVideos: null;
    },
  }
}
</script>

<style scoped>
.fixed {
  position: fixed;
  top: 0;
}
</style>
