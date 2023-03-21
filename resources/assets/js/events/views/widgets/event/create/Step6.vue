<template>
    <div class="b-box">
        <div class="add-event-summary">
            <button class="b-btn__icon back-button" type="button" @click="goStepBack('/')">
                <svg class="icon"><use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_back')"></use></svg>
            </button>

            <div class="add-event-summary__title">
                {{ title }}
            </div><!--w-form__text-->

            <div class="add-event-summary__cols">
                <div class="add-event-summary__col1">
                    <div>{{ event_date | formatDate('day-date') }}</div>
                    <div>{{ time }}</div>
                    <div>{{ location }}</div>
                    <div v-if="address_type == 'city_only'">{{ locality }}</div>
                    <div v-else>{{ address }}</div>
                </div>

                <div class="add-event-summary__col2" v-if="preview_photo.id">
                    <img :src="preview_photo.photo_small" alt="" />
                    <span v-if="type === 'friends'">{{ trans('events.type.friends') }}</span>
                    <span v-else-if="type === 'fun'">{{ trans('events.type.fun') }}<i v-if="chemsfriendly"> (cf)</i></span>
                </div>
            </div>

            <div class="add-event-summary__description">{{ description }}</div>

            <div class="w-tags" v-if="tags.length">
                <span class="b-label main label" v-for="tag in tags">
                    #{{ tag }}
                </span>
            </div>

            <div class="w-user__photos" v-if="selectedPhotosArray.length">
                <figure
                    class="b-user__photo user__photos-width square-container"
                    v-for="photo in selectedPhotosArray"
                >
                    <div
                        class="square-container-inner"
                        :style="{'background': `url(${photo.photo_small}) center / cover`}"
                    ></div>
                </figure>
            </div>

            <div class="w-user__photos" v-if="selectedVideosArray.length">
                <figure
                    class="b-user__photo user__photos-width square-container"
                    v-for="video in selectedVideosArray"
                >
                    <div
                        class="square-container-inner"
                        :style="{'background': `url(${video.thumb_small}) center / cover`}"
                    ></div>
                </figure>
            </div>

        </div>

        <div class="row align-middle">
            <div :class="{'columns': true, 'small-12': isDesktop, 'small-12': isMobile}" class="text-center margin-top-15">
                <button class="bb-button-green" id="button6" type="button" @click="submit">{{ trans('submit_event') }}</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .w-tags .label.main {
        padding: 7px 15px !important;
    }
    .space-between {
        height: 50px;
    }
    .user__events-width {
        margin: 0 2px 2px 1px !important;
    }
    .user__events--oversize{
        display: flex;
        margin-bottom: 5px;
        height: 120px;
        overflow-x: auto;
        overflow-y: hidden;
    }
        .user__events--oversize figure{
            width: 100px!important;
            height: 100px;
            float: left;
            flex: none;
        }
    .back-button {
        position: absolute;
        left: 0;
        padding: 0 !important;
        top: 7px;
    }
    .back-button svg {
        fill: #fff !important;
        height: 24px !important;
        width: 24px !important;
        margin-left: 3px !important;
    }

    .w-tags .label {
        font-size: 16px;
    }
    .w-user__photos {
        margin-top: 30px;
    }
</style>

<script>
    import _ from 'lodash';
    import qs from 'qs';
    import {mapActions} from 'vuex';
    import eventsModule from '@events/module/store/type'

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['vars'],
        data() {
            return this.vars
        },
        computed: {
            photos() {
                return this.$parent.photos;
            },
            videos() {
                return this.$parent.videos;
            },
            selectedPhotosArray() {
                return this.$parent.selectedPhotosArray;
            },
            selectedVideosArray() {
                return this.$parent.selectedVideosArray;
            }
        },
        methods: {
            ...mapActions({
                setEvent: eventsModule.actions.setEvent,
                saveEvent: eventsModule.actions.events.submit
            }),
            setPreviewPhoto(photo) {
                this.preview_photo = photo
            },
            async submit() {
                let event = _.pick(this, [
                    'title', 'description', 'event_date', 'type', 'chemsfriendly',
                    'is_profile_linked', 'time', 'location', 'locality', 'state',
                    'country', 'country_code', 'address', 'lat', 'lng', 'address_type',
                    'tags', 'preview_photo'
                ])
                event.photos = this.selectedPhotosArray.map(e => e.id)
                event.videos = this.selectedVideosArray.map(e => e.id)

                this.showLoadingButton('#button6')
                await this.saveEvent(event)
                this.restoreLoadingButton('#button6')
            },
            goStepBack() {
                this.scrollEventsPageTop(1);
                this.step = 5;
            }
        }
    }
</script>
