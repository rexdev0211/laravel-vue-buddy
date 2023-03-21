<template>
    <div class="secondary-menu-nested">
        <div class="secondary-menu location">
            <div class="secondary-menu-header">
                <i class="back" @click="closeProfileLocation"></i>
                <div class="title">{{ trans('location') }}</div>
            </div>

            <vue-custom-scrollbar ref="vueCustomScrollBar" class="secondary-menu-body">
                <ProfileLocationForms @refreshScroll="refreshScroll"/>
            </vue-custom-scrollbar>

        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex';

    import ProfileLocationForms from '@profile/views/widgets/form/ProfileLocationForms.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            ProfileLocationForms,
            vueCustomScrollbar
        },
        data() {
            return {
              resizeWindow: this.$resize
            }
        },
        methods: {
            ...mapActions([
                'closeProfileLocation'
            ]),
            refreshScroll() {
                this.$refs.vueCustomScrollBar.$forceUpdate()
            }
        },
        watch: {
          '$resize': function () {
              this.$refs.vueCustomScrollBar.$forceUpdate()
          }
        }
    }
</script>
<style lang="scss">
.ps-container {
  .ps__rail-y {
    z-index: 1015 !important;
  }
}
</style>