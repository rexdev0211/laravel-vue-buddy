<template>
  <MediaWatcher ref="mediaWatcherVideo" :items="videos" type="video" :fullscreen="true"></MediaWatcher>
</template>

<script>
    import MediaWatcher from "./MediaWatcher";
    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                viewVideoRevealVisible: false,
                viewVideoId: 0
            }
        },
        components: {
            MediaWatcher
        },
        mounted() {
            app.$on('show-view-video-reveal', this.showEditVideoReveal);
        },
        beforeDestroy() {
            app.$off('show-view-video-reveal');
        },
        props: ['videos'],
        methods: {
            showEditVideoReveal(viewVideoId) {
                this.$refs.mediaWatcherVideo.show(viewVideoId)
            },
        },
    }
</script>
