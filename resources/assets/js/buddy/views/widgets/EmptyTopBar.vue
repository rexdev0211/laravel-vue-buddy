<template>
    <transition :name="'fade'" mode="out-in">
        <div v-if="show" class="w-navbar">
            <div class="b-navbar centered">
                <div class="navbar-left">
                    <button class="b-btn__icon profileLink" type="button" @click="goToMyProfile">
                        <svg v-if="discreetModeEnabled" class="icon icon-discreet_on"><use v-bind:xlink:href="symbolsSvgUrl('icon-discreet_on')"></use></svg>
                        <svg v-else class="icon icon-profile"><use v-bind:xlink:href="symbolsSvgUrl('icon-profile')"></use></svg>
                    </button>
                </div>
            </div><!--b-navbar-->
        </div><!--w-navbar-->
    </transition>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';

    export default {
        data() {
            return {
                show: true,
                scroll: 0
            }
        },
        mixins: [
	        require('@general/lib/mixin').default,
        ],
        computed: {
            ...mapGetters([
                'discreetModeEnabled'
            ])
        },
		methods: {
            ...mapActions([
                'goToMyProfile',
            ]),
            checkScroll() {
                if (this.$refs.mobileScrollTopContainer) {
                    let currentScroll = this.$parent.$refs.mobileScrollTopContainer.scrollTop

                    if (currentScroll > this.scroll && currentScroll > 50 && this.show) {
                        this.show = false
                    } else if (currentScroll < this.scroll && !this.show || currentScroll <= 50) {
                        this.show = true
                    }

                    this.scroll = currentScroll
                }
            },
        },
        mounted() {
            let v = this
            if (app.isMobile && this.$parent.$refs && this.$parent.$refs.mobileScrollTopContainer) {
                this.$parent.$refs.mobileScrollTopContainer.addEventListener('scroll', function() {
                    v.checkScroll()
                })
            }
        },
    }
</script>
