<template>
    <div id="add_home_screen_popup" :class="{'showAtBottom': showOn == 'homepage'}" v-show="showPopup">
        <div class="text-right close-popup" @click="closePopup"> &times; </div>
        <div class="container">
            <img src="/apple-touch-icon.png" class="logo" />
            <div>
                <div v-html="trans('install_pwa_ios_message')"></div>
                <!--<img style="height: 40px" :src="`/assets/img/add-home-screen-${lang}.png`" />-->
            </div>
        </div>
    </div>
</template>

<style scoped>
    #add_home_screen_popup {
        position: fixed;
        bottom: 48px;
        background: #f6f5ec;
        border: 1px solid #999;
        z-index: 5001;
        padding: 5px 15px;
    }
    .showAtBottom {
        bottom: 0 !important;
    }
    .container {
        display: flex;
        margin: 5px 0;
        align-items: center;
        margin-right: 15px;
        color: #0a0a0a;
    }
    .container .logo {
        margin-right: 15px;
    }
    .close-popup {
        color: #666;
        font-size: 2em;
        line-height: 1;
        font-weight:400;
        position: absolute;
        right: 10px;
    }
</style>

<script>


    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                showPopup: false,
                homeScreenKey: 'add-home-screen-' + this.showOn
            }
        },
        props: {
            showOn: {
                type: String,
                required: true
            }
        },
        methods: {
            closePopup() {
                this.showPopup = false;

                localStorage.setItem(this.homeScreenKey, 'closed')
            },
            checkShowPopup() {
                //app.log('checking home screen');

                //don't show for desktop or app version
                if (this.isDesktop || this.isApp) {
                    return;
                }

                let newPopupState = false;

                // Detects if device is in standalone mode
                const isInStandaloneMode = () => ('standalone' in window.navigator) && (window.navigator.standalone);

                // Checks if should display install popup notification:
                if (this.isIos() && !isInStandaloneMode() && !localStorage.getItem(this.homeScreenKey)) {
                    newPopupState = true;
                }

                //do we need to modify it?
                if (newPopupState) {
                    this.showPopup = newPopupState;
                }
            },
        },
        mounted() {
            //this.checkShowPopup();
        }
    }
</script>