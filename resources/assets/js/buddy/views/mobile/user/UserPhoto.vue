<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu media-watching">

                <div class="secondary-menu-header">
                    <i class="back" @click="goBackToUser"></i>
                    <div class="title" v-if="!dataLoading">{{ trans('pos_of_total', {position: photoPosition, total: photos.length}) }}</div>
                </div>

                <div class="secondary-menu-body">

                    <div class="w-swiper swiper-container">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper" @click="zoomSwiper()">
                            <!-- Slides -->
                            <div class="swiper-slide" v-if="!dataLoading" v-for="(photo, index) in photos" :key="index">
                                <div class="swiper-zoom-container b-profile__photo media">
                                    <img class="img" :src="photo.photo_orig">
                                </div>
                            </div>
                        </div>
                    </div><!--swiper-container-->

                </div><!--secondary-menu-body-->
            </div><!--secondary-menu-->
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['userToken', 'photoId'],
        data() {
            return {
                pid: this.photoId,
                delay: 500,
                clicks: 0,
                timer: null,
                isZoomed: false,
            }
        },
        computed: {
            dataLoading() {
                return !this.user;
            },
            user(){
                return this.$store.getters.getUser(this.userToken)
            },
            userId(){
                return this.user ? this.user.id : null
            },
            photos() {
                let v = this
                if (this.isApp) {
                    return this.user && this.user.public_photos.filter(photo => v.photoIsSafe(photo, true))
                } else {
                    return this.user && this.user.public_photos;
                }
            },
            photo() {
                return this.photos && this.photos.find(el => el.id == this.pid);
            },
            photoPosition() {
                return this.photos && this.photos.indexOf(this.photo) + 1;
            }
        },
        methods: {
            goBackToUser() {
                if (window.history.length > 1) {
                    this.$router.go(-1);
                } else {
                    this.goBack(`/user/${userId}`)
                }
            },
            onPhotoChanged(newIndex, zoomed = false) {
                this.pid = this.photos[newIndex].id;
                this.isZoomed = zoomed;
            },
            onPhotoZoomed(zoomed) {
                this.isZoomed = zoomed;
            },
            loadSwiperScript() {
                this.loadSwiper(this.photoPosition - 1, this.onPhotoChanged);
            },
            zoomSwiper() {
                if (this.isIos()) {
                    this.clicks++
                    if (this.clicks === 1) {
                      this.timer = setTimeout(() => {
                          this.clicks = 0
                      }, this.delay)
                    } else {
                        if (this.isZoomed) {
                          this.swiperZoomOut(this.onPhotoZoomed)
                        } else if (!this.isZoomed) {
                          this.swiperZoomIn(this.onPhotoZoomed)
                        }
                    }
                }
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
            if (!this.dataLoading) {
               this.loadSwiperScript();
            }
            if (!!this.userId && !this.user){
                this.$store.dispatch('loadUserInfo', this.userId)
            }
        }
    }
</script>
<style scoped>
.media-watching {
  height: 100vh;
}
</style>