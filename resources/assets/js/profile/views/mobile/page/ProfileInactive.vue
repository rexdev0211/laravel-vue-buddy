<template>

    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu settings-menu delete-profile">

                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/discover')"></i>
                    <div class="title">{{ trans('settings') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfileInactiveForms></ProfileInactiveForms>
                </div>

            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfileInactiveForms from '@profile/views/widgets/form/ProfileInactiveForms.vue';

    export default {
        mixins: [
            require('@general/lib/mixin').default,
        ],
        components: {
            ProfileInactiveForms,
        }
    }
</script>
