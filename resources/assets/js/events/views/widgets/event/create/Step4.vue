<template>
    <div class="b-box">
        <div class="w-form__text w-form__text--full text-center">
            {{ trans('event_choose_photos') }}
        </div><!--w-form__text-->

        <ul class="b-photos-header">
            <li>
                <svg class="icon icon-profile_active"><use v-bind:xlink:href="symbolsSvgUrl('icon-profile_active')"></use></svg>
                {{ trans('preview_picture') }}
            </li>

            <li>
                <svg class="icon icon-eye_active"><use v-bind:xlink:href="symbolsSvgUrl('icon-eye_active')"></use></svg>
                {{ trans('public_pictures') }}
                <span v-if="!userIsPro">
                    {{ trans('can_select_count', {count: selectedPhotosCount, total: maxPhotosAmount}) }}
                </span>
            </li>
        </ul><!--b-stats__list-->

        <form data-abide2 novalidate data-vv-scope="form4" @submit.prevent="goToStep5">
            <div class="w-photo__tab no-padding" id="photo__tab">
                <div class="w-user__photos w-user__photos--no-height">
                    <button class="b-btn__photo-add user__photos-width" id="addPhotoButton" @click.prevent="choosePhoto">
                        <svg class="icon icon-plus"><use v-bind:xlink:href="symbolsSvgUrl('icon-plus')"></use></svg>
                    </button><!--b-btn__photo-add-->

                    <input type="file" id="newPhotoUpload" class="show-for-sr" name="photo" accept="image/*" ref="photo" v-on:change="uploadProfilePhoto($event, true)">

                    <label class="b-user__photo user__photos-width" v-for="photo in photos">
                        <div class="b-user__photo-icons">
                            <svg v-show="preview_photo.id == photo.id" class="icon icon-profile_active" @click.stop=""><use v-bind:xlink:href="symbolsSvgUrl('icon-profile_active')"></use></svg>
                            <svg v-show="preview_photo.id != photo.id" class="icon icon-profile" @click.stop="setPreviewPhoto(photo)"><use v-bind:xlink:href="symbolsSvgUrl('icon-profile')"></use></svg>
                            <svg v-show="preview_photo.id != photo.id && selectedPhotos[photo.id]" @click.stop="removeEventPhoto(photo.id)" class="icon icon-eye_active"><use v-bind:xlink:href="symbolsSvgUrl('icon-eye_active')"></use></svg>
                            <svg v-show="preview_photo.id != photo.id && !selectedPhotos[photo.id] && leftPublicPicturesCount" @click.stop="addEventPhoto(photo.id)" class="icon icon-eye"><use v-bind:xlink:href="symbolsSvgUrl('icon-eye')"></use></svg>
                        </div>

                        <img :src="photo.photo_small" :src-big="photo.photo_orig" :id="`profile-photo-${photo.id}`" />
                    </label>
                </div><!--w-user__photos-->
            </div>

            <div class="row align-middle margin-top-30">
                <div class="text-center" :class="{'columns': true, 'small-12': isDesktop, 'small-12': isMobile}">
                    <button class="bb-button-green" id="button4" type="button" @click="goToStep5">{{ trans('reg.next') }}</button>
                </div>
            </div>
        </form>
    </div>
</template>

<style lang="css">
    @import "../../../../../_general/lib/slim/slim.min.css";
</style>

<script>
    import {mapState} from 'vuex';

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
            photos() {
                return this.$parent.photos;
            },
            selectedPhotosArray() {
                return this.$parent.selectedPhotosArray;
            },
            selectedPhotosCount() {
                return this.selectedPhotosArray.length;
            },
            maxPhotosAmount() {
                return window.MAX_EVENT_PHOTOS;
            },
            leftPublicPicturesCount() {
                return !this.userIsPro ? this.maxPhotosAmount - this.selectedPhotosCount : 1
            }
        },
        methods: {
            addEventPhoto(id) {
                this.$set(this.selectedPhotos, id, true);
            },
            removeEventPhoto(id) {
                Vue.delete(this.selectedPhotos, id);
            },
            choosePhoto() {
                this.$refs.photo.click();
            },
            setPreviewPhoto(photo) {
                this.preview_photo = photo;
                Vue.delete(this.selectedPhotos, photo.id);
            },
            goToStep5() {
                let callback = () => {
                    return this.$validator.validateAll('form4').then((result) => {
                        if (result) {
                            if (!_.isEmpty(this.preview_photo)) { //this.selectedPhotosArray.length ||
                                this.scrollEventsPageTop(1);
                                this.step = 5
                            } else {
                                this.showErrorNotification('please_select_preview_picture');
                            }
                        }
                    });
                };

                this.runLoadingFunction('#button4', callback);
            },
        }
    }
</script>
