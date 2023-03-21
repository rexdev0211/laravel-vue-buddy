<template>
  <MediaWatcher ref="mediaWatcherVideo" :items="videos" type="video" :fullscreen="true"></MediaWatcher>
</template>

<script>
    import chatModule from '@chat/module/store/type';
    import MediaWatcher from "@buddy/views/widgets/MediaWatcher";

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                msgId: null,
                videos: false
            }
        },
        components: {
            MediaWatcher
        },
        mounted() {
            app.$on('show-view-event-chat-video-reveal', this.showEditVideoReveal);
        },
        beforeDestroy() {
            app.$off('show-view-event-chat-video-reveal');
        },
        methods: {
            showEditVideoReveal(eventId, userId, msgId) {
                const videos = this.$store.state.chatModule.chat.event.videos[`${eventId}-${userId}`];

                if (videos === undefined) {
                    app.showLightLoading(true)

                    axios.get(`/api/getEventChatVideosList/${eventId}/${userId}`)
                        .then(({data}) => {
                            const payload = {
                                userId: userId,
                                eventId: eventId,
                                videos: data
                            }

                            this.$store.commit(chatModule.mutations.messages.setVideosEvent, payload)

                            app.showLightLoading(false)

                            this.initializeVariables(data, msgId)
                        })
                } else {
                    this.initializeVariables(images, msgId)
                }
            },
            initializeVariables(videos, msgId) {
                this.msgId = msgId;

                this.videos = videos;

                this.$refs.mediaWatcherVideo.show(msgId);

                //to make left/right keys work
                setTimeout(() => {
                    $('.bb-popup').focus();
                }, 0)
            }
        }
    }
</script>