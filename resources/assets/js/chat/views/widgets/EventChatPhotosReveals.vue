<template>
    <MediaWatcher ref="mediaWatcherPhoto" :items="photos" type="photo" :fullscreen="true"></MediaWatcher>
</template>

<script>
    import chatModule from '@chat/module/store/type';
    import MediaWatcher from "@buddy/views/widgets/MediaWatcher";

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                msgId: null,
                photos: false
            }
        },
        components: {
            MediaWatcher
        },
        mounted() {
            app.$on('show-view-event-chat-photo-reveal', this.showEditPhotoReveal);
        },
        beforeDestroy() {
            app.$off('show-view-event-chat-photo-reveal');
        },
        methods: {
            showEditPhotoReveal(eventId, userId, msgId) {
                const images = this.$store.state.chatModule.chat.event.images[`${eventId}-${userId}`];

                if (images === undefined) {
                    app.showLightLoading(true)

                    axios.get(`/api/getEventChatImagesList/${eventId}/${userId}`)
                        .then(({data}) => {
                            const payload = {
                                userId: userId,
                                eventId: eventId,
                                images: data
                            }
                            this.$store.commit(chatModule.mutations.messages.setImagesEvent, payload)

                            app.showLightLoading(false)

                            this.initializeVariables(data, msgId)
                        })
                } else {
                    this.initializeVariables(images, msgId)
                }
            },
            initializeVariables(photos, msgId) {
                this.msgId = msgId;

                this.photos = photos;

                this.$refs.mediaWatcherPhoto.show(msgId);

                //to make left/right keys work
                setTimeout(() => {
                    $('.bb-popup').focus();
                }, 0)
            }
        }
    }
</script>