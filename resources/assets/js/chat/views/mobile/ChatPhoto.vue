<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu media-watching">

                <div class="secondary-menu-header">
                    <i class="back" @click="goToUserChat();"></i>
                    <div class="title" v-if="!dataLoading">{{ trans('pos_of_total', {position: photoPosition, total: photos.length}) }}</div>
                </div>

                <div class="secondary-menu-body">

                    <div class="w-swiper swiper-container">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">
                            <!-- Slides -->
                            <div class="swiper-slide" v-if="!dataLoading" v-for="(photo, index) in photos" :key="index">
                                <div class="swiper-zoom-container b-profile__photo media">
                                    <div class="swiper-zoom-target img"
                                        :style="{'background': `url(${photo.photo_orig}) no-repeat center / contain`}">
                                    </div>
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
    import chatModule from '@chat/module/store/type';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['userToken', 'msgId'],
        data() {
            return {
                messageId: false,
            }
        },
        methods: {
            goToUserChat() {
                this.$destroy();

                if (window.history.length > 1) {
                    this.$router.go(-1);
                } else {
                    let userToken = this.user.link || this.user.id
                    this.goTo('/chat/' + userToken);
                }
            },
            loadSwiperScript() {
                this.loadSwiper(this.photoPosition - 1, this.onPhotoChanged);
            },
            onPhotoChanged(newIndex) {
                this.messageId = this.photos[newIndex].id;
            }
        },
        computed: {
            dataLoading() {
                return !this.user || !this.photos
            },
            photo() {
                return this.photos && this.photos.find(el => el.id == this.messageId);
            },
            photoPosition() {
                return this.photos && this.photo && this.photos.indexOf(this.photo) + 1;
            },
            photos() {
                if (!this.allPhotos) {
                    return false
                }
                const firstPhoto = this.allPhotos.find(v => v.id == this.msgId)
                return this.allPhotos.filter(v => v.user_from == firstPhoto.user_from)
            },
            user(){
                return this.$store.getters.getUser(this.userToken)
            },
            userId(){
                return this.user ? this.user.id : null
            },
            ...mapState({
                allPhotos: function(state) {
                    if (this.user) {
                        const images = state.chatModule.chat.user.images[this.user.id]
                        if (images === undefined) {
                            axios.get(`/api/getChatImagesList/${this.user.id}`)
                                .then(({data}) => {
                                    const payload = {
                                        userId: this.user.id,
                                        images: data
                                    }

                                    this.$store.commit(chatModule.mutations.messages.setImages, payload)
                                })
                            return false
                        }
                        return images
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
        mounted(){
            if (!this.dataLoading) {
                this.loadSwiperScript()
            }
        }
    }
</script>