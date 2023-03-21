<template>

  <div class="w-root">
    <div class="w-views">
      <div class="secondary-menu settings-menu delete-profile">
        <div class="secondary-menu-header">
          <i class="back" @click="goTo('/profile/settings')"></i>
          <div class="title">{{ trans('delete_all_sharing_links') }}</div>
        </div>

        <div class="secondary-menu-body">
          <DeleteAllSharingLinksForms></DeleteAllSharingLinksForms>
        </div>
      </div>
    </div><!--w-views-->
  </div><!--w-root-->
</template>

<script>
import DeleteAllSharingLinksForms from '@profile/views/widgets/form/DeleteAllSharingLinksForms.vue';
import ProfileDeleteReveals from '@profile/views/widgets/reveal/ProfileDeleteReveals.vue';
import ProfileDeactivateReveals from '@profile/views/widgets/reveal/ProfileDeactivateReveals.vue';

export default {
  mixins: [require('@general/lib/mixin').default],
  components: {
    DeleteAllSharingLinksForms,
    ProfileDeleteReveals,
    ProfileDeactivateReveals
  },
}
</script>
