<template>
    <div class="secondary-menu-nested">
        <div class="secondary-menu edit-profile">
            <div class="secondary-menu-header">
                <i class="back" @click="closeProfileEdit"></i>
                <div class="title">{{ trans('main_my_profile') }}</div>
            </div>

            <vue-custom-scrollbar :settings="scrollBarSettings" class="secondary-menu-body">
                <ProfileEditForms></ProfileEditForms>
            </vue-custom-scrollbar>

        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex';

    import ProfileEditForms from '@profile/views/widgets/form/ProfileEditForms.vue';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            ProfileEditForms,
            vueCustomScrollbar
        },
        data() {
            return {
              scrollBarSettings: {
                suppressScrollY: false,
                suppressScrollX: true
              }
            }
        },
        mounted () {
          app.$on('show-scroll', this.showScroll);
        },
        methods: {
            ...mapActions([
                'closeProfileEdit'
            ]),
            showScroll(slot) {
              this.scrollBarSettings.suppressScrollY = !slot;
            },
        }
    }
</script>