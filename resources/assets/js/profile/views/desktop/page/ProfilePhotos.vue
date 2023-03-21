<template>
    <div class="secondary-menu-nested">
        <div class="secondary-menu photos">
            <div class="secondary-menu-header">
                <i class="back" @click="closeProfilePhotos"></i>
                <div class="title">{{ trans('photos') }}</div>
            </div>

            <vue-custom-scrollbar ref="vueCustomScrollbar" class="secondary-menu-body">
                <ProfilePhotosForm/>
            </vue-custom-scrollbar>
        </div>

        <transition :name="'fade'" mode="out-in" type="animation" :duration="500">
            <ProfilePhotosReveals></ProfilePhotosReveals>
        </transition>
    </div>
</template>

<script>
    import {mapActions, mapState} from 'vuex';

    import ProfilePhotosForm from '@profile/views/widgets/form/ProfilePhotosForms.vue';
    import ProfilePhotosReveals from '@profile/views/widgets/reveal/ProfilePhotosReveals.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            ProfilePhotosForm,
            ProfilePhotosReveals,
            vueCustomScrollbar
        },
        methods: {
            ...mapActions([
                'closeProfilePhotos'
            ]),
        },
        computed: {
          ...mapState({
            photos: 'profilePhotos',
          })
        },
        watch: {
          photos() {
            this.$refs.vueCustomScrollbar.$forceUpdate();
          }
        }
    }
</script>