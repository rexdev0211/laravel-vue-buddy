<template>
    <div class="video-wrapper">
        <video :playsinline="isMobile" autoplay muted controls :poster="poster" ref="video" controlsList="nodownload">
            <source :src="videoSource.mp4" type="video/mp4"/>
            <source :src="videoSource.webm" type="video/webm" v-if="!isSafari"/>
        </video>
    </div>
</template>

<style scoped>
    .video-wrapper > video {
        width: 100%;
        vertical-align: middle;
        max-width: 100%;
        max-height: 100%;
    }
    .video-wrapper > video.has-media-controls-hidden::-webkit-media-controls {
        display: none;
    }
    .video-overlay-play-button {
        box-sizing: border-box;
        width: 100%;
        height: 100%;
        padding: 10px calc(50% - 50px);
        position: absolute;
        top: 0;
        left: 0;
        display: block;
        /*opacity: 0.95;*/
        cursor: pointer;
        /*background-image: linear-gradient(transparent, transparent, transparent, #000);*/
        transition: opacity 150ms;
        --color: #fff;
    }
    .video-overlay-play-button:hover {
        opacity: 1;
    }
    .video-overlay-play-button.is-hidden {
        display: none;
    }
</style>

<script>
    //https://codepen.io/chrisnager/pen/jPrJgQ

    export default {
        props: ['poster', 'videoSource', 'videoId'],
        mixins: [require('@general/lib/mixin').default],
        methods: {
            playback(payload) {
                if (this.videoId != payload.playVideoId) {
                    this.pauseVideo()
                } else {
                    this.playVideo()
                }
            },
            pauseVideo() {
                this.$refs.video.pause()
            },
            playVideo() {
                this.$refs.video.play()
            }
        },
        watch: {
            videoSource: function(newValue, oldValue){
                this.$refs.video.load()
            }
        },
        mounted(){
            app.$on('video-playback', this.playback)
        },
        destroyed() {
            app.$off('video-playback')
        },
        activated(){
            this.playVideo()
        }
    }
</script>
