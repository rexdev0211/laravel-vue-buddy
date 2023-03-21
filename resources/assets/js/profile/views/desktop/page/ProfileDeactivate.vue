<template>
    <div class="secondary-menu settings-menu delete-profile">
        <div class="secondary-menu-header">
            <i class="back" @click="closeProfileDeactivation"></i>
            <div class="title">{{ trans('settings') }}</div>
        </div>

        <vue-custom-scrollbar ref="vueCustomScrollbar" class="secondary-menu-body">
            <ProfileDeactivateForms></ProfileDeactivateForms>
        </vue-custom-scrollbar>

        <div class="secondary-menu-body alert-screen">
            <ProfileDeleteReveals></ProfileDeleteReveals>
            <ProfileDeactivateReveals></ProfileDeactivateReveals>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';

    import ProfileDeactivateForms from '@profile/views/widgets/form/ProfileDeactivateForms.vue';
    import ProfileDeleteReveals from '@profile/views/widgets/reveal/ProfileDeleteReveals.vue';
    import ProfileDeactivateReveals from '@profile/views/widgets/reveal/ProfileDeactivateReveals.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            ProfileDeactivateForms,
            ProfileDeleteReveals,
            ProfileDeactivateReveals,
            vueCustomScrollbar
        },
        methods: {
            ...mapActions([
                'closeProfileDeactivation'
            ]),
        },
        computed: {
            ...mapGetters([
                'profileDeactivationOpened'
            ]),
        },
        watch: {
            '$resize': function () {
                this.$refs.vueCustomScrollbar.$forceUpdate()
            }
        }
    }
</script>
<style lang="scss">
.ps__rail-y {
  z-index: 1015 !important;
}
</style>