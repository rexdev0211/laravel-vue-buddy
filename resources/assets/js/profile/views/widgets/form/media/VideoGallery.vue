<template>
    <transition :name="'slide-in-bottom'" mode="out-in">
        <div class="add-pics-box video"
            v-if="visible"
            @click.self="hide"
            tabindex="0">
            <div class="wrapper">
                <div class="inner">
                    <div class="close" @click="hide"></div>
                    <div class="pics-catalog">
                        <div class="pic upload-photo"
                            id="addVideoButton"
                            @click="chooseVideo">
                            <input type="file" id="newVideoUpload" class="show-for-sr" name="video" accept="video/*" ref="video" v-on:change="uploadVideo($event)">
                        </div>
                        <div v-if="!videos || !videos.length" class="add-media-hover">
                            <span>{{ trans('add_video') }}</span>
                        </div>
                        <div class="pic"
                            v-for="(video, index) in videos"
                            @click="selectMedia(video.id, index, video.active, $event)"
                            :class="{'selected': video.active}"
                            :style="{'background': !video.id ? 'url(/assets/img/preloader.svg) center no-repeat' : 'none'}">
                            <div class="img"
                                v-if="!!video.id"
                                :id="`profile-video-${video.id}`"
                                :style="{'background': `url(${video.thumb_small}) center / cover`}">
                            </div>
                        </div>
                    </div>
                    <div class="float-button">
                        <button class="btn" type="submit" @click="submitVideos($event)">{{ trans('ok') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';

    export default {
        mixins: [
            require('@general/lib/mixin').default
        ],
        data() {
            return {
                visible: false,
                selectedVideos: [],
                videosCount: 0,
                videos: null,
            }
        },
        mounted() {
            if (app.isDesktop) {
                this.videos = this.privateVideos;
            }

            app.$on('show-video-gallery', this.show);
        },
        beforeDestroy() {
            app.$off('show-video-gallery');
        },
        methods: {
            ...mapActions([
                'requirementsAlertShow'
            ]),
            chooseVideo() {
                this.$refs.video.click();
            },
            show(){
                app.$emit('show-scroll', false);

                this.visible = true
            },
            hide() {
                app.$emit('show-scroll', true);

                this.videosCount = 0;
                this.selectedVideos = [];

                if (this.videos) {
                    this.videos.map(video => {
                        video.active = false;
                    });
                }

                this.visible = false
            },
            submitVideos(event) {
                if (this.selectedVideos.length === 0) {
                    this.hide();
                }

                this.selectedVideos.forEach(element => {
                    this.makeVideoPublic(element, event);
                });
                this.videosCount = 0;
                this.selectedVideos = [];
            },
            selectMedia(id, index, active, event) {

                if (!this.userIsPro && this.videosCount === 4 && !active) {
                  this.$store.dispatch('requirementsAlertShow', 'media')
                  return;
                }

                if (!id) {
                    return;
                }

                let video = this.videos[index];
                let videos = this.videos;

                video.active = !video.active;

                if (video.active) {
                    this.selectedVideos.push(video.id);
                    this.videosCount++;
                } else {
                    this.removeByValue(this.selectedVideos, video.id);
                    this.videosCount--;
                }

                this.$set(videos, index, video);
            },
            removeByValue(arr) {
              let what, a = arguments, length = a.length, ax;
              while (length > 1 && arr.length) {
                what = a[--length];
                while ((ax = arr.indexOf(what)) !== -1) {
                  arr.splice(ax, 1);
                }
              }
              return arr;
            },
            makeVideoPublic(video, event) {
                if (!this.userIsPro && !this.leftPublicVideosCount) {
                    this.requirementsAlertShow('media')
                } else {
                    this.makeVideoVisibleTo(video, 'public', event)
                    this.hide()
                }
            }
        },
        computed: {
            ...mapGetters({
                leftPublicVideosCount: 'leftPublicVideosCount',
                publicVideosCount: 'publicVideosCount',
                privateVideos: 'privateVideos'
            })
        },
        watch: {
            visible: function (newVal) {
                if (newVal) {
                    this.videosCount = this.publicVideosCount;
                }
            },
            privateVideos: function (newVal) {
                if (newVal) {
                    this.videos = newVal;
                }
            }
        }
    }
</script>