<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu media-watching video">

                <div class="secondary-menu-header">
                    <i class="back" @click="goBackToChat"></i>
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
    import {mapState} from 'vuex';
    import VideoPlayer from '@buddy/views/widgets/VideoPlayer.vue';
    import chatModule from '@chat/module/store/type';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['eventId', 'msgId'],
        data() {
            return {
                messageId: false
            }
        },
        components: {
            VideoPlayer
        },
        methods: {
            goBackToChat() {
                if (window.history.length > 1) {
                    this.$router.go(-1);
                } else {
                    this.goTo(`/chat/group/${this.eventId}`);
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
            dataLoading() {
                return !this.event || !this.videos
            },
            video() {
                return this.videos && this.videos.find(el => el.id == this.messageId);
            },
            videoPosition() {
                return this.videos && this.video && this.videos.indexOf(this.video) + 1;
            },
            event() {
                return this.$store.getters.getEvent(this.eventId)
            },
            ...mapState({
                videos(state) {
                    if (this.event) {
                        const videos = state.chatModule.chat.event.videos[this.eventId]
                        if (videos === undefined) {
                            axios.get(`/api/getGroupChatVideosList/${this.eventId}`)
                                .then(({data}) => {
                                    const payload = {
                                        eventId: this.eventId,
                                        videos: data,
                                    }
                                    this.$store.commit(chatModule.mutations.messages.setVideosEvent, payload)
                                })
                            return false;
                        }
                        return videos
                    }
                    return false
                }
            }),
        },
        created() {
            this.messageId = this.msgId;
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
            if (!this.dataLoading) {
                this.loadSwiperScript();
            }
        }
    }
</script>