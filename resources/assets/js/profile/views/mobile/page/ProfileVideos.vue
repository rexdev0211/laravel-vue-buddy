<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu videos">

                <div class="secondary-menu-header">
                    <i class="back" @click="getUrlBack"></i>
                    <div class="title">{{ trans('videos') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfileVideosForm
                        :urlSharingModelParent="urlSharingModeParent"
                        :haveSharingVideosParent="haveSharingVideosParent"
                        :selectedVideosParent="selectedVideosParent"
                        @toggleSharingMode="toggleSharingMode($event)"
                        >
                    </ProfileVideosForm>
                </div>

            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfileVideosForm from '@profile/views/widgets/form/ProfileVideosForms.vue';
    import {mapActions} from "vuex";

    export default {
      data() {
        return {
          urlSharingModeParent: false,
          haveSharingVideosParent: false,
          selectedVideosParent: [],
        }
      },
        mixins: [
            require('@general/lib/mixin').default,
        ],
        components: {
            ProfileVideosForm
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
            this.goTo('/discover')
            // this.closeProfileVideos();
          }

        }
      }
    }
</script>
