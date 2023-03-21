<template>
    <div class="w-app">
        <TopBarDesktop/>
        <ProfileMenuDesktop tab="info"/>

        <main class="w-content">
            <div id="content" class="b-content">
                <div class="inner__col">
                    <div class="w-row row">
                        <ProfileInfoForms/>
                    </div><!--row-->
                </div><!--inner__col-->
            </div><!--b-content-->
        </main><!--w-content-->

        <ProfilePasswordReveals></ProfilePasswordReveals>
    </div><!--w-app-->
</template>

<script>
    import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';

    import ProfileMenuDesktop from '@profile/views/desktop/menu/ProfileMenuDesktop.vue';
    import ProfileInfoForms from '@profile/views/widgets/form/ProfileInfoForms.vue';
    import ProfilePasswordReveals from '@profile/views/widgets/reveal/ProfilePasswordReveals.vue';

    export default {
        components: {
            TopBarDesktop,
            ProfileMenuDesktop,
            ProfileInfoForms,
            ProfilePasswordReveals
        }
    }
</script>