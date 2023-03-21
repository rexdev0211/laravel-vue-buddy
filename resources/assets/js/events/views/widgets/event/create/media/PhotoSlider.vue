<template>
  <div class="row profile-photos">
    <div class="item pic"
         :class="{'added': photo.id}"
         v-for="(photo, index) in items"
         @click="!photo.id ? showPhotoGallery() : false">
      <div v-if="photo.id"
           :id="`profile-photo-${photo.id}`"
           class="img"
           :style="{'background': `url(${photo.photo_small}) center / cover`}"
           :src-big="photo.photo_orig">
      </div>
      <div v-else class="img"></div>
      <div class="close" v-if="photo.id" :title="trans('delete_photo')"
           @click.prevent="makePhotoPrivate(photo, index, $event)">
      </div>
    </div>
  </div>
</template>

<style lang="css">
@import "../../../../../../_general/lib/slim/slim.min.css";
</style>

<script>
import {mapActions, mapState, mapGetters} from 'vuex';
import slim from '@general/lib/slim/slim.vue';

export default {
  mixins: [require('@general/lib/mixin').default],
  data() {
    return {
      croppers: [],
      editPhotoId: null,
      slimOptions: this.defaultSlimOptions(),
    }
  },
  props: ['photos'],
  components: {
    slim
  },
  methods: {
    ...mapActions([
      'requirementsAlertShow',
    ]),
    choosePhoto() {
      this.$refs.photo.click();
    },
    showPhotoGallery(){
      if (!this.userIsPro && !this.$parent.leftPublicPicturesCount === 0) {
        this.$store.dispatch('requirementsAlertShow', 'media')
      } else {
        app.$emit('show-photo-event-gallery', null)
      }
    },
    goToPhoto(photoId) {
      if (this.isMobile) {
        this.$router.push(`/profile/photo/${photoId}`);
      } else {
        app.$emit('show-edit-photo-reveal', photoId);
      }
    },
    makePhotoPrivate(photo, index, event) {
      this.$parent.setDeletePhotos(photo, index);
    },
  },
  computed: {
    ...mapState({
      userIsPro: 'userIsPro',
    }),
    ...mapGetters({
      leftPublicPicturesCount: 'leftPublicPicturesCount',
      privatePhotos: 'privatePhotos',
      publicPhotos: 'publicPhotos',
      publicPhotosCount: 'publicPhotosCount',
    }),
    items(){
      let items = []
      let placeholdersArray = []
      let placeholdersArrayLength = 1

      if (this.photos?.length > 0) {
          let publicPhotosLength = this.photos.length;
          placeholdersArrayLength = publicPhotosLength < 5 ? 5 - publicPhotosLength : 1;
          placeholdersArray = Array.from({length: placeholdersArrayLength}, (_, i) => {return {}})

          items.push(...this.photos, ...placeholdersArray)
          return items;
      } else {
          if (!this.userIsPro) {
            placeholdersArrayLength = this.$parent.leftPublicPhotosCount + 1
          } else {
            placeholdersArrayLength = this.$parent.activePhotosCount < 5 ? 5 - this.$parent.activePhotosCount : 1
          }
          placeholdersArray = Array.from({length: placeholdersArrayLength}, (_, i) => {return {}})
          items.push(...this.photos, ...placeholdersArray)
          return items
      }
    },
  }
}
</script>
