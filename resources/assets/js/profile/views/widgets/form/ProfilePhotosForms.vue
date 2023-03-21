<template>
    <div>
        <div class="wrap">
            <div class="pics-catalog">
                <div class="pic upload-photo"
                    id="addPhotoButton"
                    @click="choosePhoto">
                    <input type="file" id="newPhotoUpload" class="show-for-sr" name="photo" accept="image/*" ref="photo" v-on:change="uploadProfilePhoto($event, true)">
                </div>
                <div class="pic" v-for="photo in photos" @click="goToPhoto(photo.id)">
                    <div class="img"
                    :id="`profile-photo-${photo.id}`"
                    :style="{'background': `url(${photo.photo_small}) center / cover`}"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="css">
    @import "../../../../_general/lib/slim/slim.min.css";
</style>

<script>
    import {mapState, mapActions, mapGetters} from 'vuex';
    import slim from '@general/lib/slim/slim.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                croppers: [],
                editPhotoId: null,
                slimOptions: this.defaultSlimOptions(),
            }
        },
        components: {
            slim
        },
        methods: {
            ...mapActions([
                'requirementsAlertShow',
            ]),
            choosePhoto() {
                this.$refs.photo.click();
            },
            goToPhoto(photoId) {
                if (this.isMobile) {
                    this.$router.push(`/profile/photo/${photoId}`);
                } else {
                    app.$emit('show-edit-photo-reveal', photoId);
                }
            },
            changePhotoVisibility(photo, state, event) {
                if (state == 'public' && !this.userIsPro && this.leftPublicPicturesCount == 0) {
                    this.requirementsAlertShow('media')
                } else {
                    this.makePhotoVisibleTo(photo, state, event)
                }
            },
        },
        computed: {
            dataLoading() {
                return this.$parent.dataLoading;
            },
            ...mapState({
                photos: 'profilePhotos',
                userIsPro: 'userIsPro',
            }),
            ...mapGetters([
                'leftPublicPicturesCount'
            ])
        }
    }
</script>
