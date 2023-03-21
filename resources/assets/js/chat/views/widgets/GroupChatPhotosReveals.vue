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
            app.$on('show-view-group-chat-photo-reveal', this.showEditPhotoReveal);
        },
        beforeDestroy() {
            app.$off('show-view-group-chat-photo-reveal');
        },
        methods: {
            showEditPhotoReveal(eventId, msgId) {
                const images = this.$store.state.chatModule.chat.event.images[eventId];

                if (images === undefined) {
                    app.showLightLoading(true)

                    axios.get(`/api/getGroupChatImagesList/${eventId}`)
                        .then(({data}) => {
                            const payload = {
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
        },
    }
</script>
<style>
  .media-watching {
      top: 0 !important;
      position: fixed !important;
  }
</style>