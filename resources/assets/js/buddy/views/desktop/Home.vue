<template>
    <div class="w-app column__layout fixed-bg-container" id="js-login-page">
        <HomeForms></HomeForms>
    </div><!--w-app-->
</template>

<script>
    import HomeForms from '@buddy/views/widgets/HomeForms.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            HomeForms
        }
    }
</script>