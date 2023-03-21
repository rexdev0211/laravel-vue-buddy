<template>

    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu settings-menu delete-profile">
                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/profile/settings')"></i>
                    <div class="title">{{ trans('settings') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfileDeactivateForms></ProfileDeactivateForms>
                </div>

                <div class="secondary-menu-body alert-screen">
                    <ProfileDeleteReveals></ProfileDeleteReveals>
                    <ProfileDeactivateReveals></ProfileDeactivateReveals>
                </div>
            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfileDeactivateForms from '@profile/views/widgets/form/ProfileDeactivateForms.vue';
    import ProfileDeleteReveals from '@profile/views/widgets/reveal/ProfileDeleteReveals.vue';
    import ProfileDeactivateReveals from '@profile/views/widgets/reveal/ProfileDeactivateReveals.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            ProfileDeactivateForms,
            ProfileDeleteReveals,
            ProfileDeactivateReveals
        }
    }
</script>
