<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu media-watching video">

                <div class="secondary-menu-header">
                    <i class="back" @click="goToUserChat"></i>
                    <div class="title" v-if="!dataLoading">{{ trans('pos_of_total', {position: videoPosition, total: videos.length}) }}</div>
                </div>

                <div class="secondary-menu-body">

                    <div class="w-swiper swiper-container" dir="rtl">
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
    import VideoPlayer from '@buddy/views/widgets/VideoPlayer.vue';
    import chatModule from '@chat/module/store/type';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['userToken', 'msgId'],
        data() {
            return {
                videos: [],
                dataLoading: true,
                messageId: false
            }
        },
        components: {
            VideoPlayer
        },
        methods: {
            goToUserChat() {
                if (window.history.length > 1) {
                    this.$router.go(-1);
                } else {
                    let userToken = this.user.link || this.user.id
                    this.goTo('/chat/' + userToken);
                }
            },
            loadSwiperScript() {
                this.loadSwiper(this.videoPosition - 1, this.onVideoChanged);
            },
            onVideoChanged(newIndex) {
                this.messageId = this.videos[newIndex].id;
            },
        },
        computed: {
            user(){
                return this.$store.getters.getUser(this.userToken)
            },
            userId(){
                return this.user ? this.user.id : null
            },
            video() {
                return this.videos && this.videos.find(el => el.id == this.messageId);
            },
            videoPosition() {
                return this.videos && this.video && this.videos.indexOf(this.video) + 1;
            }
        },
        created() {
            this.messageId = this.msgId;

            const videos = this.$store.state.chatModule.chat.user.videos[this.userId]

            if (videos === undefined) {
                axios.get(`/api/getChatVideosList/${this.userId}`)
                    .then(({data}) => {
                        const payload = {
                            userId: this.userId,
                            videos: data
                        }

                        this.$store.commit(chatModule.mutations.messages.setVideos, payload)
                        this.videos = data;
                        this.dataLoading = false;
                    })
            } else {
                this.videos = videos;

                this.dataLoading = false;
            }
        },
        watch: {
            dataLoading: function (loading) {
                if (!loading) {
                    this.$nextTick(function(){
                        this.loadSwiperScript();
                    })
                }
            }
        },
        mounted() {
            if(!this.dataLoading) {
                this.loadSwiperScript();
            }
            if (!!this.userToken && !this.user){
                this.$store.dispatch('loadUserInfo', this.userToken)
            }
        }
    }
</script>