<template>
    <div id="application-wrapper">
        <TopBarDesktop tab="pro"/>

        <div class="secondary-menu go-pro">
            <vue-custom-scrollbar :settings="settings" ref="vueCustomScrollbar" class="secondary-menu-body">
                <ProfileSubscriptionCreate/>
            </vue-custom-scrollbar>
        </div>
    </div>
</template>

<script>
    import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';
    import ProfileSubscriptionCreate from '@profile/views/widgets/subscription/ProfileSubscriptionCreate.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"
    import {mapState} from "vuex";

    export default {
        components: {
            TopBarDesktop,
            ProfileSubscriptionCreate,
            vueCustomScrollbar
        },
        data() {
          return {
            scrollDisable: true,
            settings: {
              handlers: ['click-rail', 'drag-thumb', 'keyboard', 'wheel', 'touch']
            },
            defaultHandlers: ['click-rail', 'drag-thumb', 'keyboard', 'wheel', 'touch']
          }
        },
        computed: {
          ...mapState({
            sidebar: state => state.sidebar.profile.visible
          })
        },
        methods: {
          disableScroll() {
          }
        },
        watch: {
          sidebar(newVal) {
            this.settings.handlers = newVal ? [] : this.defaultHandlers;
          },
          '$resize': function () {
            this.$refs.vueCustomScrollbar.$forceUpdate()
          }
        }
    }
</script>
<style scoped>
#application-wrapper {
  overflow: hidden;
}
</style>