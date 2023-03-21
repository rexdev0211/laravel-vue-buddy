<template>
  <MediaWatcher ref="mediaWatcherPhoto" :items="photos" type="photo" :canDelete="true"></MediaWatcher>
</template>
<script>
    import {mapState} from 'vuex';
    import MediaWatcher from "@buddy/views/widgets/MediaWatcher";
    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            MediaWatcher
        },
        data() {
            return {
                editPhotoRevealVisible: false,
                editPhotoId: 0, ////used for slim: slimInit && slimModificationsConfirmed
                slimOptions: this.defaultSlimOptions(),
                croppers: []
            }
        },
        mounted() {
            app.$on('show-edit-photo-reveal', this.showEditPhotoReveal);
        },
        beforeDestroy() {
            app.$off('show-edit-photo-reveal');
        },
        methods: {
            showEditPhotoReveal(editPhotoId) {
                this.$refs.mediaWatcherPhoto.show(editPhotoId);
            },
            changePhotoVisibility(state, event) {
                this.makePhotoVisibleTo(this.photo, state, event);
            },
        },
        computed: {
            ...mapState({
                photos: 'profilePhotos',
            }),
            isDefault() {
                return this.photo.is_default == 'yes';
            },
            isPublic() {
                return this.photo.visible_to == 'public';
            },
        }
    }
</script>
