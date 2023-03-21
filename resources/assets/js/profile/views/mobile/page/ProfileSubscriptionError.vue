<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu go-pro">

                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/profile/pro')"></i>
                </div>

                <div class="secondary-menu-body" ref="mobileScrollTopContainer">
                    <ProfileSubscriptionError/>
                </div>

                <BottomBar v-if="isMobile" tab="profile"/>
            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import BottomBar from '@buddy/views/widgets/BottomBar.vue';
    import ProfileSubscriptionError from '@profile/views/widgets/subscription/ProfileSubscriptionError.vue';

    export default {
        data() {
            return {

            }
        },
        components: {
            BottomBar,
            ProfileSubscriptionError
        },
        methods: {

        },
    }
</script>
