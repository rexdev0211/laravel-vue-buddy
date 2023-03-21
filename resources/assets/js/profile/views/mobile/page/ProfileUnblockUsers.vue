<template>
  <div class="w-root">
    <div class="w-views">
      <div class="secondary-menu unblocked-menu">

        <div class="secondary-menu-header">
          <i class="back" @click="goTo('/profile/settings')"></i>
          <div class="title">{{ trans('Blocked') }}</div>
        </div>

        <div class="secondary-menu-body">
          <ProfileUnblockUsersForm></ProfileUnblockUsersForm>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ProfileUnblockUsersForm from "@profile/views/widgets/form/ProfileUnblockUsersForm.vue";

export default {
  name: "ProfileUnblockUsers",
  mixins: [require('@general/lib/mixin').default],
  components: {
    ProfileUnblockUsersForm
  }
}
</script>

<style scoped>

</style>