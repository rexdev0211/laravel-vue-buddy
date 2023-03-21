<template>
    <div id="application-wrapper">
        <TopBarDesktop tab="pro"/>

        <div class="secondary-menu status error">
            <div class="secondary-menu-body">
                <ProfileSubscriptionError/>
            </div>
        </div>
    </div>
</template>

<script>
    import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';
    import ProfileSubscriptionError from '@profile/views/widgets/subscription/ProfileSubscriptionError.vue';

    export default {
        components: {
            TopBarDesktop,
            ProfileSubscriptionError
        }
    }
</script>
