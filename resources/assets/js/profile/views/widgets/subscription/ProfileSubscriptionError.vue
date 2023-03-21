<template>
    <div class="proPage proPage--oneAction">
        <div class="box">
            <div class="splash splash--pro">
                <img src="/images/splash/avatar_sorry.png" alt="Sorry" />
                <span>{{ trans('failed_pro_upgrade') }}</span>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['bottomMenuState'],
        mixins: [
            require('@general/lib/mixin').default,
        ],
        data() {
            return {
            }
        },
        methods: {
            unlockPaymentSystem() {
                axios.post('/api/subscription/unlock', {}).then(() => {})
            },
        },
        mounted() {
            this.unlockPaymentSystem()
        },
    }
</script>
