<template>
  <div class="secondary-menu-nested">
    <div class="secondary-menu help">
      <div class="secondary-menu-header">
        <i class="back" v-if="!this.page" @click="closeProfileHelp"></i>
        <i class="back" v-else-if="this.page" @click="clearPage()"></i>

        <div class="title">{{ this.page ? this.page.title : trans('help') }}</div>
      </div>

      <vue-custom-scrollbar ref="scrollBarMenuBody" class="secondary-menu-body" v-bind:class="{'loading': this.isLoading}" v-if="!this.page" :open="this.openPage">
        <ProfileHelpForms :open="this.openPage"></ProfileHelpForms>
      </vue-custom-scrollbar>

      <vue-custom-scrollbar :settings="scrollSettings" v-else ref="vueCustomScrollbar" class="secondary-menu-body">
        <ProfileStaticContent :page="this.page"></ProfileStaticContent>
      </vue-custom-scrollbar>

    </div>
  </div>
</template>

<script>
import {mapActions} from 'vuex';
import ProfileHelpForms from '@profile/views/widgets/form/ProfileHelpForms.vue';
import ProfileStaticContent from '@profile/views/widgets/form/ProfileStaticContent.vue';

// custom scrollbar
import vueCustomScrollbar from 'vue-custom-scrollbar'
import "vue-custom-scrollbar/dist/vueScrollbar.css"

export default {
  data() {
    return {
      page: null,
      isLoading: false,
      scrollSettings: {
        suppressScrollY: false,
        suppressScrollX: true,
        wheelPropagation: false
      }
    }
  },
  mixins: [require('@general/lib/mixin').default],
  components: {
    ProfileHelpForms,
    ProfileStaticContent,
    vueCustomScrollbar
  },
  methods: {
    ...mapActions([
      'closeProfileHelp'
    ]),
    clearPage() {
      this.page = null
    },
    openPage(slug) {
      this.isLoading = true
      axios.get('/api/getStaticPage/'+app.lang+'/'+slug)
          .then(({data}) => {
            this.page = data;
            this.isLoading = false;
          })
          .catch(e => {
            this.page = {
              content: 'Page not found',
              title: '404'
            };
            this.isLoading = false;
          })
    },
  },
  watch: {
    '$resize': function () {
      this.$refs.scrollBarMenuBody.$forceUpdate()
    }
  }
}
</script>
<style lang="scss">
// TODO: please, do not uncomment this block. This code make few modals invisible.
//.secondary-menu-body {
//  position: relative;
//}
</style>