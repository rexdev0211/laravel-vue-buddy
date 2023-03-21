<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu photos">

                <div class="secondary-menu-header">
                    <i class="back" @click="goTo('/discover')"></i>
                    <div class="title">{{ trans('photos') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfilePhotosForm/>
                </div>

            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfilePhotosForm from '@profile/views/widgets/form/ProfilePhotosForms.vue';

    export default {
        mixins: [
            require('@general/lib/mixin').default,
        ],
        components: {
            ProfilePhotosForm
        },
    }
</script>
