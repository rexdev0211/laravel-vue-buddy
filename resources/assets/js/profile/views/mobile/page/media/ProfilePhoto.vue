<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu media-watching">

                <div class="secondary-menu-header">
                    <i class="back" @click="goToPhotos"></i>
                    <div class="title" v-if="!dataLoading">{{ trans('pos_of_total', {position: photoPosition, total: photos.length}) }}</div>
                    <i class="trash" @click="deletePhoto"></i>
                </div>

                <div class="secondary-menu-body">

                    <div class="w-swiper swiper-container">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">
                            <!-- Slides -->
                            <div class="swiper-slide" v-if="!dataLoading" v-for="photo in photos">
                                <div class="swiper-zoom-container b-profile__photo media">
                                    <div class="img" :id="`profile-photo-${photo.id}`"
                                        :style="{'background': `url(${photo.photo_orig}) no-repeat center / contain`}">
                                    </div>
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

<style lang="css">
    @import "../../../../../_general/lib/slim/slim.min.css";
</style>

<script>
    import {mapState} from 'vuex';
    import slim from '@general/lib/slim/slim.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                id: this.photoId,
                editPhotoId: this.photoId, //used for slim: slimInit && slimModificationsConfirmed
                slimOptions: this.defaultSlimOptions(),
                croppers: []
            }
        },
        props: ['photoId'],
        components: {
            slim
        },
        computed: {
            dataLoading() {
                return this.photo === undefined;
            },
            ...mapState({
                photos: 'profilePhotos',
            }),
            photo() {
                return this.photos.find(p => p.id == this.id);
            },
            photoPosition() {
                return this.photos.indexOf(this.photo) + 1;
            },
            isDefault() {
                return this.photo && this.photo.is_default == 'yes';
            },
            isPublic() {
                return this.photo && this.photo.visible_to == 'public';
            },
        },
        methods: {
            goToPhotos(force = false) {
                if (window.history.length > 1 && !force) {
                    this.$router.go(-1);
                } else {
                    this.$router.push('/profile/photos');
                }
            },
            onPhotoChanged(newIndex) {
                this.id = this.photos[newIndex].id;
            },
            changePhotoVisibility(state, event) {
                this.makePhotoVisibleTo(this.photo, state, event);
            },
            deletePhoto(event) {
                let position = this.photos.indexOf(this.photo);
                const photoId = this.photo.id

                let callback = () => {
                    return axios.get(`/api/photos/delete/${photoId}`).then(() => {
                        this.photos.splice(position, 1)
                        this.removePhotoFromEvents(photoId)
                        this.goToPhotos(true);
                    });
                };

                let self = this
                this.$store.dispatch('showDialog', {
                    mode: 'confirm',
                    message: this.trans('sure_delete_photo'),
                    callback: () => { self.runLoadingFunction(event.target, callback); }
                })
            },
            loadSwiperScript() {
                this.loadSwiper(this.photoPosition - 1, this.onPhotoChanged);
            }
        },
        mounted() {
            if(!this.dataLoading) {
                this.loadSwiperScript();
            } else {
                app.$on('profileLoaded', function(){
                    this.loadSwiperScript();
                });
            }
        },
        beforeDestroy() {
            app.$off('profileLoaded')
        }
    }
</script>
