<template>
    <div class="fullscreen-preloader-layer">
        <img class="preloader-logo" src="/assets/img/icons/startup-logo.png" alt=""/>
        <div class="spinner-container">
            <img class="preloader"
                src="/assets/img/preloader.svg"
                alt="">
        </div>
        <noscript>
            <div style="margin-top: 30px">
                Please enable JavaScript to use this app.
            </div>
        </noscript>
    </div>
</template>

<script>
    export default {
    }
</script>

<style scoped>
    .fullscreen-preloader-layer {
        color: #fff;
        text-align: center;
        background: var(--bb-darkgrey);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100vw;
        height: 100vh;
    }
    .preloader-logo {
        margin-bottom: 30px;
        width: 100px;
    }
</style>