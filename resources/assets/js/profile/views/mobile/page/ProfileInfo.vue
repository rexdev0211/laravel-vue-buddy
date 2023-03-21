<template>
    <div class="w-root">
        <div class="w-views">
            <div class="w-view">
                <ProfileNavBar/>

                <div class="w-pages">
                    <div class="b-page">
                        <div class="w-page__content w-page__content--no-bottom" ref="mobileScrollTopContainer">
                            <div class="b-box b-box__small">
                                <div class="row">

                                    <ProfileInfoForms/>

                                </div><!--row-->
                            </div>
                        </div><!--b-login__page-->
                    </div><!--b-page-->
                </div><!--w-pages-->

            </div><!--w-view-->
        </div><!--w-views-->

        <ProfilePasswordReveals></ProfilePasswordReveals>
    </div><!--w-root-->
</template>

<script>
    import ProfileNavBar from '@profile/views/widgets/ProfileNavBar.vue';
    import ProfileInfoForms from '@profile/views/widgets/form/ProfileInfoForms.vue';
    import ProfilePasswordReveals from '@profile/views/widgets/reveal/ProfilePasswordReveals.vue';

    export default {
        components: {
            ProfileNavBar,
            ProfileInfoForms,
            ProfilePasswordReveals
        },
    }
</script>
