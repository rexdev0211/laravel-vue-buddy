<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu settings-menu">

                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/discover')"></i>
                    <div class="title">{{ trans('settings') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfileSettingsForms></ProfileSettingsForms>
                </div>

                <ProfilePasswordReveals/>
            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfileSettingsForms from '@profile/views/widgets/form/ProfileSettingsForms.vue';
    import ProfilePasswordReveals from '@profile/views/widgets/reveal/ProfilePasswordReveals.vue';

    export default {
        mixins: [
            require('@general/lib/mixin').default
        ],
        components: {
            ProfileSettingsForms,
            ProfilePasswordReveals
        }
    }
</script>
