<template>

    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu settings-menu delete-profile">

                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/discover')"></i>
                    <div class="title">{{ trans('settings') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfileCancelForms/>
                </div>

            </div>
        </div><!--w-views-->

        <ProfileCancelReveals/>
    </div><!--w-root-->
</template>

<script>
    import ProfileCancelForms from '@profile/views/widgets/form/ProfileCancelForms.vue';
    import ProfileCancelReveals from '@profile/views/widgets/reveal/ProfileCancelReveals.vue';

    export default {
        components: {
            ProfileCancelForms,
            ProfileCancelReveals
        },
        mixins: [
          require('@general/lib/mixin').default
        ],
    }
</script>
