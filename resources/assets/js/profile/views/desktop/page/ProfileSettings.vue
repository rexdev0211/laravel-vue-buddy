<template>
    <div class="secondary-menu-nested">
        <div class="secondary-menu settings-menu">
            <div class="secondary-menu-header">
                <i class="back" @click="closeProfileSettings"></i>
                <div class="title">{{ trans('settings') }}</div>
            </div>

            <vue-custom-scrollbar class="secondary-menu-body">
                <ProfileSettingsForms></ProfileSettingsForms>
            </vue-custom-scrollbar>

            <ProfilePasswordReveals/>
            <ProfileUnblockUsersReveals/>

          <transition :name="'slide-in-left'" mode="out-in" type="animation">
            <ProfileDeactivate v-if="profileDeactivationOpened"></ProfileDeactivate>
          </transition>

          <transition :name="'slide-in-left'" mode="out-in" type="animation">
            <CustomizeSharingLinks v-if="customizeSharingLinksOpened"></CustomizeSharingLinks>
          </transition>

          <transition :name="'slide-in-left'" mode="out-in" type="animation">
            <DeleteAllSharingLinks v-if="deleteAllSharingLinksOpened"></DeleteAllSharingLinks>
          </transition>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from 'vuex';

    import ProfileDeactivate from '@profile/views/desktop/page/ProfileDeactivate.vue';

    import ProfileSettingsForms from '@profile/views/widgets/form/ProfileSettingsForms.vue';
    import ProfilePasswordReveals from '@profile/views/widgets/reveal/ProfilePasswordReveals.vue';
    import ProfileUnblockUsersReveals from "@profile/views/widgets/reveal/ProfileUnblockUsersReveals";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"
    import DeleteAllSharingLinks from "./DeleteAllSharingLinks";
    import CustomizeSharingLinks from "./CustomizeSharingLinks";

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
          CustomizeSharingLinks,
          DeleteAllSharingLinks,
            ProfileDeactivate,
            ProfileSettingsForms,
            ProfilePasswordReveals,
            ProfileUnblockUsersReveals,
            vueCustomScrollbar
        },
        computed: {
            ...mapGetters([
                'profileDeactivationOpened',
                'customizeSharingLinksOpened',
                'deleteAllSharingLinksOpened',
            ]),
        },
        methods: {
            ...mapActions([
                'closeProfileSettings'
            ]),
        }
    }
</script>