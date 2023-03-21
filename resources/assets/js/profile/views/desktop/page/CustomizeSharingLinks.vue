<template>
  <div class="secondary-menu settings-menu delete-profile sharing-links">
    <div class="secondary-menu-header">
      <i class="back" @click="closeCustomizeSharingLinks"></i>
      <div class="title">Sharing links</div>
    </div>

    <vue-custom-scrollbar ref="vueCustomScrollbar" class="secondary-menu-body">
      <CustomizeSharingLinksForms></CustomizeSharingLinksForms>
    </vue-custom-scrollbar>
  </div>
</template>

<script>
import {mapActions, mapGetters} from 'vuex';

// custom scrollbar
import vueCustomScrollbar from 'vue-custom-scrollbar'
import "vue-custom-scrollbar/dist/vueScrollbar.css"
import CustomizeSharingLinksForms from "../../widgets/form/CustomizeSharingLinksForms";

export default {
  mixins: [require('@general/lib/mixin').default],
  components: {
    CustomizeSharingLinksForms,
    vueCustomScrollbar
  },
  methods: {
    ...mapActions([
      'closeCustomizeSharingLinks',
    ]),
  },
  computed: {
    ...mapGetters([
      'deleteAllSharingLinksOpened'
    ]),
  },
  watch: {
    '$resize': function () {
      this.$refs.vueCustomScrollbar.$forceUpdate()
    }
  }
}
</script>
<style lang="scss">
.ps__rail-y {
  z-index: 1015 !important;
}
</style>