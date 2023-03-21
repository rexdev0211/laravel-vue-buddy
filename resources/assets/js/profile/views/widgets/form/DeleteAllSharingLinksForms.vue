<template>
    <div>
        <div class="box">
            <div class="headline">{{ trans('are_you_sure_you_want_to_delete_all_sharing_links') }}</div>
            <div class="text">
                <p v-html="trans('all_links_will_be_deactivated')"></p>
            </div>
        </div>
        <div class="options-box">
            <div class="options">
                <button id="deleteAllSharingLinks" class="btn darker" @click="deleteAllSharingLinks">{{ trans('delete_all') }}</button>
                <button id="cancelDeleteAllSharingLinks" class="btn" @click="closeSharingWindow">{{ trans('cancel') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import mixin from '@general/lib/mixin';

    export default {
        mixins: [
            require('@general/lib/mixin').default,
        ],
        methods: {
          ...mapActions([
              'closeDeleteAllSharingLinks',
              'confirmDeleteSharingLinks',
          ]),

          deleteAllSharingLinks() {
            this.confirmDeleteSharingLinks();
            app.$emit('allSharingLinksDeleted', true);
            mixin.methods.showSuccessNotification(app.trans('all_sharing_links_deleted_successfully'));
            this.closeSharingWindow();
          },
          closeSharingWindow() {
            if (this.isMobile) {
              this.goTo('/discover')
            } else {
              this.closeDeleteAllSharingLinks();
            }
          },
        }
    }
</script>