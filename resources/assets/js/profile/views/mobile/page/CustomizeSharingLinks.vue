<template>

    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu settings-menu delete-profile sharing-links">
                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/profile/settings')"></i>
                    <div class="title">Sharing links</div>
                </div>

                <div class="secondary-menu-body">
                    <CustomizeSharingLinksForms></CustomizeSharingLinksForms>
                </div>
            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import CustomizeSharingLinksForms from '@profile/views/widgets/form/CustomizeSharingLinksForms.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            CustomizeSharingLinksForms,
        }
    }
</script>
