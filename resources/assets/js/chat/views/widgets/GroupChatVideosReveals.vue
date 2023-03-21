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
            app.$on('show-view-group-chat-video-reveal', this.showEditVideoReveal);
        },
        beforeDestroy() {
            app.$off('show-view-group-chat-video-reveal');
        },
        methods: {
            showEditVideoReveal(eventId, msgId) {
                const videos = this.$store.state.chatModule.chat.event.videos[eventId];

                if (videos === undefined) {
                    app.showLightLoading(true)

                    axios.get(`/api/getGroupChatVideosList/${eventId}`)
                        .then(({data}) => {
                            const payload = {
                                eventId: eventId,
                                videos: data
                            }

                            this.$store.commit(chatModule.mutations.messages.setVideosEvent, payload)

                            app.showLightLoading(false)

                            this.initializeVariables(data, msgId)
                        })
                } else {
                    this.initializeVariables(videos, msgId)
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