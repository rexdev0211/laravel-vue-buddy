<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu go-pro">

                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/discover')"></i>
                </div>

                <vue-custom-scrollbar ref="vueCustomScrollbar" class="secondary-menu-body">
                  <ProfileSubscriptionCreate/>
                </vue-custom-scrollbar>

                <BottomBar v-if="isMobile" tab="profile"/>
            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import BottomBar from '@buddy/views/widgets/BottomBar.vue';
    import ProfileSubscriptionCreate from '@profile/views/widgets/subscription/ProfileSubscriptionCreate.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default
        ],
        data() {
            return {

            }
        },
        components: {
            BottomBar,
            ProfileSubscriptionCreate,
            vueCustomScrollbar,
        },
        methods: {

        },
    }
</script>
