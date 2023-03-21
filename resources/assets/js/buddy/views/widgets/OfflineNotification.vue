<template>
    <div id="app-offline" v-show="showOffline" @click="hideOfflinePopup">{{ trans('you_are_offline') }} <svg class="icon icon-close"><use v-bind:xlink:href="symbolsSvgUrl('icon-close')"></use></svg></div>
</template>

<style>
    #app-offline {
        position: fixed;
        top: 0;
        width: 100%;
        height: auto;
        padding: 15px;
        background: #d17c78;
        z-index: 5002;
        color: #fff;
    }
    #app-offline svg {
        position: absolute;
        right: 15px;
        top: 15px;
        cursor: pointer;
    }
</style>

<script>
    import auth from '@general/lib/auth';

    export default {
        mixins: [require('@general/lib/mixin').default],
        data() {
            return {
                showOffline: false
            }
        },
        methods: {
            hideOfflinePopup() {
                this.showOffline = false;
                this.checkIfReloadPage();
            },
            showOfflinePopup() {
                this.showOffline = true;
            },
            checkIfReloadPage() {
                if (navigator.onLine && auth.isAuthenticated()) {
                    window.location.reload();
                }
            }
        },
        mounted() {
            window.addEventListener('offline', this.showOfflinePopup);

            window.addEventListener('online', this.hideOfflinePopup);

            if (navigator.onLine === false) {
                this.showOfflinePopup();
            }
        }
    }
</script>
