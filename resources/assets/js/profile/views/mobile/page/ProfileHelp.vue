<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu help">

                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/discover')"></i>
                    <div class="title">{{ trans('help') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfileHelpForms></ProfileHelpForms>
                </div>

            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfileHelpForms from '@profile/views/widgets/form/ProfileHelpForms.vue';

    export default {
        mixins: [
            require('@general/lib/mixin').default,
        ],
        components: {
            ProfileHelpForms
        }
    }
</script>
