<template>
    <div class="reveal-overlay" :id="`reveal-overlay-${revealId}`">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        props: ['revealId', 'isVisible'],
        watch: {
            isVisible: {
                immediate: true,
                handler (newValue, oldValue) {
                    this.$nextTick(function(){
                        if (newValue) {
                            $(`#${this.revealId}`).show();
                            $(`#reveal-overlay-${this.revealId}`).show();
                        } else {
                            $(`#${this.revealId}`).hide();
                            $(`#reveal-overlay-${this.revealId}`).hide();
                        }
                    })
                }
            }
        },
        mounted() {
            $(`#reveal-overlay-${this.revealId}`).click((e) => {
                if (
                    !!$(e.target).prop('class')
                    &&
                    $(e.target).prop('class').split(' ').includes('reveal-overlay')
                ) {
                    this.closeReveal()
                }
            });
        },
        methods: {
            closeReveal() {
                this.$emit(`close-reveal-${this.revealId}`);
            }
        }
    }
</script>
