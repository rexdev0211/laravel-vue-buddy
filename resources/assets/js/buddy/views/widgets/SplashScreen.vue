<template>
    <div class="splash-screen" v-if="splashScreenVisible" @click="hideSplashScreen">
        <img :src="splashImage" />
    </div>
</template>

<script>
    export default {
        data() {
            return {
                //dynamic variables
                splashName: 'splashScreenViewed6',
                splashImage: '/assets/img/green-komm.jpg',
                showInCountries: ['DE', 'BE', 'NL'],

                //static variables
                showSplashScreen: false
            }
        },
        created() {
            this.showSplashScreen = !localStorage.getItem(this.splashName)
        },
        computed: {
            splashScreenVisible() {
                return this.showSplashScreen && this.showInCountries.includes(this.$store.state.profile.country_code)
            }
        },
        methods: {
            hideSplashScreen() {
                localStorage.setItem(this.splashName, true);

                this.showSplashScreen = !localStorage.getItem(this.splashName);
            }
        },
    }
</script>