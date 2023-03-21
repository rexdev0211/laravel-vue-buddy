<template>
    <CustomReveal
        revealId="cancel-subscription"
        :isVisible="visible"
        v-on:close-reveal-cancel-subscription="hidePopup">
        <div
            class="w-form__reveal text-center reveal"
            id="cancel-subscription"
            data-reveal2>
            <h3 class="title">{{ trans('are_you_sure_cancel_pro') }}</h3>
            <div class="row">
                <div class="small-6 columns">
                    <a id="cancel_submit" class="bb-button-green" @click="cancelSubscription">{{ trans('yes') }}</a>
                </div>
                <div class="small-6 columns">
                    <a class="bb-button-grey" data-close @click="hidePopup">{{ trans('no') }}</a>
                </div>
            </div>
        </div>
    </CustomReveal>
</template>

<script>
    import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        data: () => ({
            visible: false
        }),
        components: {
            CustomReveal
        },
        methods: {
            showPopup() {
                this.visible = true;
                $('.w-pages').css('z-index', 9999);
            },
            hidePopup() {
                this.visible = false;
                $('.w-pages').css('z-index', "auto");
            },
            async cancelSubscription() {
                this.showLoadingButton('#cancel_submit')
                let response = await axios.post('/api/subscription/cancel', {})
                if (response.status === 200) {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect
                    } else {
                        this.goTo('/discover')
                    }
                }
                this.restoreLoadingButton('#cancel_submit')
            },
        },
        mounted() {
            app.$on('show-cancel-subscription-form', this.showPopup);
        },
        beforeDestroy() {
            app.$off('show-cancel-subscription-form');
        },
    }
</script>