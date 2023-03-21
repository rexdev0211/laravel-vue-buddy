<template>
    <div id="application-wrapper">
        <TopBarDesktop tab="deactivate"/>

        <div class="secondary-menu settings-menu delete-profile">
            <div class="secondary-menu-body">
                <ProfileCancelForms/>
            </div>

            <div class="secondary-menu-body alert-screen">
                <ProfileCancelReveals/>
            </div>
        </div>
    </div>
</template>

<script>
    import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';
    import ProfileCancelForms from '@profile/views/widgets/form/ProfileCancelForms.vue';
    import ProfileCancelReveals from '@profile/views/widgets/reveal/ProfileCancelReveals.vue';

    export default {
        components: {
            TopBarDesktop,
            ProfileCancelForms,
            ProfileCancelReveals
        }
    }
</script>