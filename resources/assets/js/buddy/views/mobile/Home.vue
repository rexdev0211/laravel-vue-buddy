<template>
    <div class="w-root">
        <div class="w-views">
            <div class="w-view">
                <div class="w-pages">
                    <div class="b-page">
                        <div class="b-login__page w-page__content" id="js-login-page">
                            <HomeForms></HomeForms>
                        </div><!--b-login__page-->
                    </div><!--b-page-->
                </div><!--w-pages-->
            </div><!--w-view-->
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<style scoped>
    .b-page {
        background: #292929;
    }
</style>

<script>
    import HomeForms from '@buddy/views/widgets/HomeForms.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            HomeForms,
        }
    }
</script>