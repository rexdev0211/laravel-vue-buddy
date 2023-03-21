<template>
    <div class="w-root">
        <div class="w-views">
            <div class="secondary-menu location">

                <div class="secondary-menu-header">
                    <i class="back" @click="goToDiscover"></i>
                    <div class="title">{{ trans('location') }}</div>
                </div>

                <div class="secondary-menu-body">
                    <ProfileLocationForms/>
                </div>

            </div>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfileLocationForms from '@profile/views/widgets/form/ProfileLocationForms.vue';
    import discoverModule from '@discover/module/store/type';

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-discover').default
        ],
        components: {
            ProfileLocationForms,
        },
        methods:{
          goToDiscover() {
            this.goTo('/discover')
          },
        },
    }
</script>
<style scoped>
  .child-view {
      position: relative;
  }
</style>