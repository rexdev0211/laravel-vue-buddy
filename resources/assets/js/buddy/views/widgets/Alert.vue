<template>
    <div class="bb-popup alert-popup is-mobile-visible" @click.self="hideAlert" tabindex="0" :class="{'hidden': isHideAvailable && !isVisible}">
        <div class="bb-popup__container bb-popup__container--alert" v-bind:class="{'isClearStyle': clear, 'isFullSize': isFullSize}">
            <div @click="hideAlert" class="close"></div>

            <div class="popup" v-if="!clear">

                <div class="popup-inner">
                    <slot></slot>
                </div>

            </div>
            <slot v-else></slot>
        </div>
    </div>
</template>

<script>
    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['clear', 'isVisible', 'isHideAvailable', 'isFullSize'],
        methods: {
            hideAlert() {
                this.$emit('hide-alert')
            }
        },
    }
</script>
