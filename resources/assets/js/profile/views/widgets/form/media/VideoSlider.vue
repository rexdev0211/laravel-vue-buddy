<template>
    <div class="row profile-videos">
        <div class="item vid"
            :class="{'added': video.id}"
            v-for="video in items"
            @click="!video.id ? showVideoGallery() : false">
            <div v-if="video.id" :id="`profile-video-${video.id}`"
                class="img"
                :style="{'background': `url(${video.thumb_small}) center / cover`}">
            </div>
            <div v-else class="img"></div>
            <div class="close" v-if="video.id" ::title="trans('delete_video')"
                @click.prevent="makeVideoPrivate(video, $event)">
            </div>
        </div>
    </div>
</template>

<script>
    import discoverModule from '@discover/module/store/type'
    import {mapState, mapActions, mapGetters} from 'vuex';

    export default {
        mixins: [require('@general/lib/mixin').default],
        methods: {
            ...mapActions({
                requirementsAlertShow: 'requirementsAlertShow',
            }),
            showVideoGallery(){
                if (!this.userIsPro && !this.leftPublicVideosCount) {
                    this.$store.dispatch('requirementsAlertShow', 'media')
                } else {
                    app.$emit('show-video-gallery')
                }
            },
            chooseVideo() {
                this.$refs.video.click();
            },
            deleteVideo(event) {
                let position = this.videos.indexOf(this.video);
                const videoId = this.video.id

                let callback = () => {
                    return axios.get(`/api/videos/delete/${videoId}`)
                        .then(() => {
                            this.videos.splice(position, 1);
                            this.$store.dispatch(discoverModule.actions.users.update, {
                                userId: auth.getUserId(),
                                fields: {
                                    has_videos: !!this.publicVideos.length
                                }
                            })

                            this.removeVideoFromEvents(videoId)
                            this.goToVideos();
                        });
                };

                let self = this
                this.$store.dispatch('showDialog', {
                    mode: 'confirm',
                    message: this.trans('sure_delete_video'),
                    callback: () => { self.runLoadingFunction(event.target, callback); }
                })
            },
            makeVideoPrivate(video, event) {
                this.makeVideoVisibleTo(video.id, 'private', event)
            },
        },
        computed: {
            ...mapState({
                userIsPro: 'userIsPro',
            }),
            ...mapGetters([
                'leftPublicVideosCount',
                'publicVideos',
                'publicVideosCount',
            ]),
            items(){
                let items = []
                let placeholdersArray = []
                let placeholdersArrayLength = 1
                if (!this.userIsPro) {
                    placeholdersArrayLength = this.leftPublicVideosCount + 1
                } else {
                    placeholdersArrayLength = this.publicVideosCount < 5 ? 5 - this.publicVideosCount : 1
                }
                placeholdersArray = Array.from({length: placeholdersArrayLength}, (_, i) => {return {}})
                items.push(...this.publicVideos, ...placeholdersArray)
                return items
            },
        }
    }
</script>
