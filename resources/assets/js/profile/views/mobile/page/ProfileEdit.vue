<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu edit-profile">
                <div class="secondary-menu-header">
                    <i class="back" @click="goTo(prevPage)"></i>
                    <div class="title">{{ trans('main_my_profile') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfileEditForms></ProfileEditForms>
                </div>

            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfileEditForms from '@profile/views/widgets/form/ProfileEditForms.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            ProfileEditForms
        },
        data() {
            return {
                prevPage: null
            }
        },
        beforeRouteEnter(to, from, next) {
            if (!from.fullPath.match(/\/user\//)) { // if user came NOT from discover
              next((vm) => {
                vm.prevPage = from.fullPath
              })
            } else {
              next((vm) => {
                vm.prevPage = '/discover'
              })
            }
        }
    }
</script>
