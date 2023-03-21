<template>
    <section id="blog">
        <div class="back">
            <a href="/" @click.prevent="goBack('/')"></a>
        </div>
        <div class="blog-wrapper">
            <div class="blog-inner">
                <h1>{{ page.title }}</h1>
                <div class="content" v-html="page.content"></div>
            </div>
            <div class="load-more">
                <div class="down"></div>
            </div>
        </div>
    </section>
</template>

<script>
    export default {
        data: () => ({
            page: {}
        }),
        props: ['slug'],
        mixins: [
            require('@general/lib/mixin').default
        ],
        created() {
            console.log('[Page] Created', { slug: this.slug })

            app.showLoading(true)

            axios.get(`/api/getStaticPage/${app.lang}/${this.slug}`)
                .then(({data}) => {
                    this.page = data
                    app.showLoading(false)
                })
                .catch(e => {
                    this.page = {
                        content: 'Page not found',
                        title: '404'
                    }
                    app.showLoading(false)
                })
        },
        mounted(){
            console.log('[Page] Mounted', { slug: this.slug })
        }
    }
</script>