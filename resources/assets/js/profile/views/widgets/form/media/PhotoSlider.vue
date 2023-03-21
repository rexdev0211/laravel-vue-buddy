<template>
    <div class="row profile-photos">
        <div class="item pic"
            :class="{'added': photo.id}"
            v-for="photo in items"
            @click="!photo.id ? showPhotoGallery() : false">
            <div v-if="photo.id" :id="`profile-photo-${photo.id}`"
                class="img"
                :style="{'background': `url(${photo.photo_small}) center / cover`}"
                :src-big="photo.photo_orig">
            </div>
            <div v-else class="img"></div>
            <div class="close" v-if="photo.id" :title="trans('delete_photo')"
                @click.prevent="makePhotoPrivate(photo, $event)">
            </div>
        </div>
    </div>
</template>

<style lang="css">
    @import "../../../../../_general/lib/slim/slim.min.css";
</style>

<script>
    import {mapActions, mapState, mapGetters} from 'vuex';
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
            showPhotoGallery(){
                if (!this.userIsPro && !this.leftPublicPicturesCount) {
                    this.$store.dispatch('requirementsAlertShow', 'media')
                } else {
                    app.$emit('show-photo-gallery', null)
                }
            },
            goToPhoto(photoId) {
                if (this.isMobile) {
                    this.$router.push(`/profile/photo/${photoId}`);
                } else {
                    app.$emit('show-edit-photo-reveal', photoId);
                }
            },
            makePhotoPrivate(photo, event) {
                this.makePhotoVisibleTo(photo.id, 'private', event)
                    .then(response => {
                        let deletePhotoId = response.data[0].id;
                        let userInfoPhotos = this.$store.state.usersInfo[auth.getUserId()].public_photos;
                        if (userInfoPhotos) {
                            let index = userInfoPhotos.findIndex(el => el.id === deletePhotoId);
                            this.$store.state.usersInfo[auth.getUserId()].public_photos.splice(index, 1);
                        }
                    })
            },
        },
        computed: {
            ...mapState({
                photos: 'profilePhotos',
                userIsPro: 'userIsPro',
            }),
            ...mapGetters({
                leftPublicPicturesCount: 'leftPublicPicturesCount',
                privatePhotos: 'privatePhotos',
                publicPhotos: 'publicPhotos',
                publicPhotosCount: 'publicPhotosCount',
            }),
            items(){
                let items = []
                let placeholdersArray = []
                let placeholdersArrayLength = 1
                if (!this.userIsPro) {
                    placeholdersArrayLength = this.leftPublicPicturesCount + 1
                } else {
                    placeholdersArrayLength = this.publicPhotosCount < 5 ? 5 - this.publicPhotosCount : 1
                }
                placeholdersArray = Array.from({length: placeholdersArrayLength}, (_, i) => {return {}})
                items.push(...this.publicPhotos, ...placeholdersArray)
                return items
            },
        }
    }
</script>
