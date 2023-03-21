<template>
  <MediaWatcher ref="mediaWatcherPhoto" :items="photos" type="photo" :fullscreen="true"></MediaWatcher>
</template>

<script>
    import MediaWatcher from "./MediaWatcher";
    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                viewPhotoRevealVisible: false,
                viewPhotoId: 0
            }
        },
        components: {
            MediaWatcher
        },
        mounted() {
            app.$on('show-view-photo-reveal', this.showEditPhotoReveal);
        },
        beforeDestroy() {
            app.$off('show-view-photo-reveal');
        },
        props: ['photos'],
        methods: {
            showEditPhotoReveal(viewPhotoId) {
                this.$refs.mediaWatcherPhoto.show(viewPhotoId);
            },
        },
    }
</script>
