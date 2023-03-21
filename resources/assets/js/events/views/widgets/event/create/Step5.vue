<template>
    <div class="b-box">
        <div class="w-form__text w-form__text--full text-center">
            {{ trans('event_choose_videos') }}
        </div><!--w-form__text-->

        <ul class="b-photos-header">
            <li>
                <svg class="icon icon-eye_active"><use v-bind:xlink:href="symbolsSvgUrl('icon-eye_active')"></use></svg>
                {{ trans('public_videos') }}
                <span v-if="!userIsPro">
                    {{ trans('can_select_count', {count: selectedVideosCount, total: maxVideosAmount}) }}
                </span>
            </li>
        </ul><!--b-stats__list-->

        <form data-abide2 novalidate data-vv-scope="form5" @submit.prevent="goToStep6">
            <div class="w-photo__tab no-padding" id="photo__tab">

                <div class="w-user__photos w-user__photos--no-height">
                    <button class="b-btn__photo-add user__photos-width" id="addVideoButton" @click.prevent="chooseVideo">
                        <svg class="icon icon-plus"><use v-bind:xlink:href="symbolsSvgUrl('icon-plus')"></use></svg>
                    </button><!--b-btn__photo-add-->

                    <input type="file" id="newVideoUpload" class="show-for-sr" name="video" accept="video/*" ref="video" v-on:change="uploadVideo($event)">

                    <figure
                        class="b-user__photo user__photos-width square-container"
                        v-for="video in videos"
                        :style="{'background': !video.id ? 'url(/assets/img/preloader.svg) center no-repeat' : 'none'}"
                    >
                        <div class="b-user__photo-icons" v-if="!!video.id" style="text-align: center;">
                            <svg v-show="selectedVideos[video.id]" @click.stop="removeEventVideo(video.id)" class="icon icon-eye_active"><use v-bind:xlink:href="symbolsSvgUrl('icon-eye_active')"></use></svg>
                            <svg v-show="!selectedVideos[video.id] && leftPublicVideosCount" @click.stop="addEventVideo(video.id)" class="icon icon-eye"><use v-bind:xlink:href="symbolsSvgUrl('icon-eye')"></use></svg>
                        </div>

                        <div
                            :id="`profile-video-${video.id}`"
                            class="square-container-inner"
                            :style="{'background': `url(${video.thumb_small}) center / cover`}"
                            v-if="!!video.id"
                        ></div>

                        <svg v-if="!!video.id" class="icon icon-video-play icon-video-play--white">
                            <use v-bind:xlink:href="symbolsSvgUrl('icon-play')"></use>
                        </svg>
                    </figure>
                </div><!--w-user__photos-->

            </div>

            <div class="row align-middle margin-top-30">
                <div class="text-center" :class="{'columns': true, 'small-12': isDesktop, 'small-12': isMobile}">
                    <button class="bb-button-green" id="button5" type="button" @click="goToStep6">{{ trans('reg.next') }}</button>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
    .square-container {
        position: relative;
    }
    .square-container:before {
        content: "";
        float: left;
        padding-top: 100%;
    }
    .square-container-inner {
        position: absolute;
        width: 100%;
        height: 100%;
    }

</style>

<script>
    import { mapState } from 'vuex';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['vars'],
        data() {
            return this.vars;
        },
        ...mapState({
            userIsPro: 'userIsPro',
        }),
        computed: {
            videos() {
                return this.$parent.videos;
            },
            selectedVideosArray() {
                return this.$parent.selectedVideosArray;
            },
            selectedVideosCount() {
                return this.selectedVideosArray.length;
            },
            maxVideosAmount() {
                return window.MAX_EVENT_VIDEOS;
            },
            leftPublicVideosCount() {
                return !this.userIsPro ? this.maxVideosAmount - this.selectedVideosCount : 1
            }
        },
        mounted() {
        },
        methods: {
            addEventVideo(id) {
                this.$set(this.selectedVideos, id, true);
            },
            removeEventVideo(id) {
                Vue.delete(this.selectedVideos, id);
            },
            chooseVideo() {
                this.$refs.video.click();
            },
            goToStep6() {
                let callback = () => {
                    return this.$validator.validateAll('form5').then((result) => {
                        if (result) {
                            this.scrollEventsPageTop(1);

                            this.step = 6
                        }
                    });
                };

                this.runLoadingFunction('#button5', callback);
            },
            goStepBack() {
                this.scrollEventsPageTop(1);
                this.step = 4;
            }
        }
    }
</script>
