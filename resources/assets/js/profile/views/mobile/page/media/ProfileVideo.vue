<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu media-watching video">

                <div class="secondary-menu-header">
                    <i class="back" @click="goToVideos"></i>
                    <div class="title" v-if="!dataLoading">{{ trans('pos_of_total', {position: videoPosition, total: videos.length}) }}</div>
                    <i class="trash" @click="deleteVideo"></i>
                </div>

                <div class="secondary-menu-body">

                    <div class="w-swiper swiper-container">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">
                            <!-- Slides -->
                            <div class="swiper-slide" v-if="!dataLoading" v-for="video in videos">
                                <div class="swiper-zoom-container b-profile__photo media">
                                    <VideoPlayer :poster="video.thumb_orig" :videoSource="video.video_url"></VideoPlayer>
                                </div>
                            </div>
                        </div>

                        <div class="w-slider__controls" v-if="!dataLoading"></div>
                    </div><!--swiper-container-->

                </div><!--secondary-menu-body-->
            </div><!--secondary-menu-->
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import {mapState} from 'vuex';
    import VideoPlayer from '@buddy/views/widgets/VideoPlayer.vue'
    import discoverModule from "@discover/module/store/type";

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                id: this.videoId,
            }
        },
        props: ['videoId'],
        components: {
            VideoPlayer
        },
        computed: {
            dataLoading() {
                return this.video === undefined;
            },
            ...mapState({
                videos: 'profileVideos',
            }),
            video() {
                return this.videos.find(p => p.id == this.id);
            },
            videoPosition() {
                return this.videos.indexOf(this.video) + 1;
            },
            isPublic() {
                return this.video && this.video.visible_to == 'public';
            },
            leftPublicVideosCount() {
                return this.$store.getters.leftPublicVideosCount;
            },
            publicVideos() {
                return this.$store.getters.publicVideos;
            },
            publicVideosCount() {
                return this.$store.getters.publicVideosCount;
            },
        },
        methods: {
            goToVideos(force = false) {
                if (window.history.length > 1 && !force) {
                    this.$router.go(-1);
                } else {
                    this.$router.push('/profile/videos');
                }
            },
            onVideoChanged(newIndex) {
                this.id = this.videos[newIndex].id;
            },
            makeVideoVisibleToLocal(state, event) {
                this.makeVideoVisibleTo(this.video, state, event);
            },
            deleteVideo(event) {
                let position = this.videos.indexOf(this.video);
                const videoId = this.video.id

                let callback = () => {
                    return axios.get(`/api/videos/delete/${videoId}`).then(() => {
                        this.videos.splice(position, 1);
                        this.$store.dispatch(discoverModule.actions.users.update, {
                            userId: auth.getUserId(),
                            fields: {
                                has_videos: !!this.publicVideos.length
                            }
                        })

                        this.removeVideoFromEvents(videoId)
                        this.goToVideos(true);
                    });
                };

                let self = this
                this.$store.dispatch('showDialog', {
                    mode: 'confirm',
                    message: this.trans('sure_delete_video'),
                    callback: () => { self.runLoadingFunction(event.target, callback); }
                })
            },
            loadSwiperScript() {
                this.loadSwiper(this.videoPosition - 1, this.onVideoChanged);
            }
        },
        mounted() {
            if (!this.dataLoading) {
                this.loadSwiperScript();
            } else {
                app.$on('profileLoaded', function(){
                    this.loadSwiperScript();
                });
            }
        },
        beforeDestroy() {
            app.$off('profileLoaded')
        }
    }
</script>
