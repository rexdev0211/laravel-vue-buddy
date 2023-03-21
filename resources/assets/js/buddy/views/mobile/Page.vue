<template>
  <div class="w-views">
    <div class="secondary-menu help page">

      <div class="secondary-menu-header">
        <i class="back" @click="goBack('/')"></i>
        <div class="title">{{ page.title }}</div>
      </div>

      <div class="secondary-menu-body">
        <div class="static-page-content" v-html="page.content"></div>
      </div>
    </div>
  </div><!--w-views-->
</template>

<script>
    export default {
        data() {
            return {
                page: {}
            }
        },
        props: ['slug'],
        mixins: [require('@general/lib/mixin').default],
        created() {
            app.showLoading(true);
            axios.get('/api/getStaticPage/'+app.lang+'/'+this.slug)
                .then(({data}) => {
                    this.page = data;
                    app.showLoading(false);
                })
                .catch(e => {
                    this.page = {
                        content: 'Page not found',
                        title: '404'
                    };
                    app.showLoading(false);
                })
        },
    }
</script>