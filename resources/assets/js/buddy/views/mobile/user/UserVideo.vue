<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu media-watching video">

                <div class="secondary-menu-header">
                    <i class="back" @click="goBackToUser"></i>
                    <div class="title" v-if="!dataLoading">{{ trans('pos_of_total', {position: videoPosition, total: videos.length}) }}</div>
                </div>

                <div class="secondary-menu-body">

                    <div class="w-swiper swiper-container">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">
                            <!-- Slides -->
                            <div class="swiper-slide" v-if="!dataLoading" v-for="video in videos">
                                <div class="swiper-zoom-container b-profile__photo media">
                                    <VideoPlayer :videoId="video.id" :poster="video.thumb_orig" :videoSource="video.video_url"></VideoPlayer>
                                </div>
                            </div>
                        </div>
                    </div><!--swiper-container-->

                </div><!--secondary-menu-body-->
            </div><!--secondary-menu-->
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import {mapState} from 'vuex';
    import VideoPlayer from '@buddy/views/widgets/VideoPlayer.vue'

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['userToken', 'videoId'],
        components: {
            VideoPlayer
        },
        data() {
            return {
                vid: this.videoId
            }
        },
        computed: {
            dataLoading() {
                return !this.user;
            },
            user(){
                return this.$store.getters.getUser(this.userToken)
            },
            userId(){
                return this.user ? this.user.id : null
            },
            videos(state) {
                return this.user && this.user.public_videos;
            },
            video() {
                return this.videos && this.videos.find(el => el.id == this.vid);
            },
            videoPosition() {
                return this.videos && this.videos.indexOf(this.video) + 1;
            }
        },
        methods: {
            goBackToUser() {
                if (window.history.length > 1) {
                    this.$router.go(-1);
                } else {
                    this.goBack(`/user/${userId}`)
                }
            },
            onVideoChanged(newIndex) {
                this.vid = this.videos[newIndex].id;
                app.$emit('video-playback', { playVideoId: this.vid})
            },
            loadSwiperScript() {
                this.loadSwiper(this.videoPosition - 1, this.onVideoChanged);
            },
        },
        watch: {
            dataLoading: function (loading) {
                if (!loading) {
                    this.$nextTick(function(){
                        this.loadSwiperScript()
                    })
                }
            }
        },
        mounted() {
            if (!this.dataLoading) {
               this.loadSwiperScript();
            }
            if (!!this.userToken && !this.user){
                this.$store.dispatch('loadUserInfo', this.userToken)
            }
        }
    }
</script>