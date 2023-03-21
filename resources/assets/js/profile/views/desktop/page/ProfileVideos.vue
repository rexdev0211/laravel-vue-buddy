<template>
    <div class="secondary-menu-nested">
        <div class="secondary-menu videos">
            <div class="secondary-menu-header">
                <i class="back" @click="getUrlBack"></i>
                <div class="title">{{ trans('videos') }}</div>
            </div>

            <vue-custom-scrollbar ref="vueCustomScrollbar" class="secondary-menu-body" :settings="scrollBarSettings">
              <ProfileVideosForm
                  :urlSharingModelParent="urlSharingModeParent"
                  :haveSharingVideosParent="haveSharingVideosParent"
                  :selectedVideosParent="selectedVideosParent"
                  @toggleSharingMode="toggleSharingMode($event)"
              >
              </ProfileVideosForm>
            </vue-custom-scrollbar>
        </div>

        <transition :name="'fade'" mode="out-in" type="animation" :duration="500">
            <ProfileVideosReveals></ProfileVideosReveals>
        </transition>
    </div>
</template>

<style>
.secondary-menu.videos {
  
}
</style>

<script>
    import {mapActions, mapState} from 'vuex';

    import ProfileVideosForm from '@profile/views/widgets/form/ProfileVideosForms.vue';
    import ProfileVideosReveals from '@profile/views/widgets/reveal/ProfileVideosReveals.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        data() {
          return {
            urlSharingModeParent: false,
            haveSharingVideosParent: false,
            selectedVideosParent: [],
            scrollBarSettings: {
              suppressScrollY: false,
              suppressScrollX: true,
            }
          }
        },
        mixins: [require('@general/lib/mixin').default],
        components: {
            ProfileVideosForm,
            ProfileVideosReveals,
            vueCustomScrollbar
        },
        methods: {
            ...mapActions([
                'closeProfileVideos'
            ]),

          toggleSharingMode(event) {
            this.urlSharingModeParent = event
          },
          getUrlBack() {
            if(this.urlSharingModeParent) {
              this.urlSharingModeParent = false
              this.haveSharingVideosParent = false
              this.selectedVideosParent = []

              app.$emit('changeUrlSharingMode')
            } else {
              // this.goTo('/discover')
              this.closeProfileVideos();
            }

          }

        },
        computed: {
          ...mapState({
            videos: 'profileVideos',
          }),
        },
        watch: {
          videos() {
            this.$refs.vueCustomScrollbar.$forceUpdate();
          }
        }
    }
</script>