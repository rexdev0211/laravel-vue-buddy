<template>
  <MediaWatcher ref="mediaWatcherVideo" :items="videos" type="video" :canDelete="true"></MediaWatcher>
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
                editVideoRevealVisible: false,
                editVideoId: 0
            }
        },
        mounted() {
            app.$on('show-edit-video-reveal', this.showEditVideoReveal);
        },
        beforeDestroy() {
            app.$off('show-edit-video-reveal');
        },
        methods: {
            showEditVideoReveal(editVideoId) {
              this.$refs.mediaWatcherVideo.show(editVideoId)
            },
            makeVideoVisibleToLocal(state, event) {
                this.makeVideoVisibleTo(this.video, state, event);
            },
        },
        computed: {
            ...mapState({
                videos: 'profileVideos',
            }),
            isPublic() {
                return this.video.visible_to == 'public';
            },
        }
    }
</script>
