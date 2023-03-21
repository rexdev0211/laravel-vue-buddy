<template>
    <div class="w-root">
        <div class="w-views">
            <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
                <div class="w-view">
                    <transition :name="'fade'" mode="out-in">
                        <div v-if="show" class="w-navbar">
                            <div class="b-navbar centered">
                                <div class="navbar-left">
                                    <button class="b-btn__icon" type="button" @click="applyFilters">
                                        <svg class="icon icon-arrow_back-navbar"><use v-bind:xlink:href="symbolsSvgUrl('icon-arrow_back')"></use></svg>
                                    </button>
                                </div>

                                <div class="text text--icon">
                                    <svg class="icon icon-variable"><use v-bind:xlink:href="symbolsSvgUrl('icon-variable')"></use></svg>
                                    {{ trans('filter') }}
                                </div>

                                <div class="navbar-right"></div>
                            </div>
                        </div><!--w-navbar-->
                    </transition>

                    <div class="w-pages">
                        <div class="b-page">
                            <div class="w-page__content w-page__content--no-bottom" ref="mobileScrollTopContainer">
                                <div class="b-box b-box__small">
                                    <div class="row">

                                        <FiltersForms></FiltersForms>

                                    </div><!--row-->
                                </div>
                            </div><!--b-login__page-->
                        </div><!--b-page-->
                    </div><!--w-pages-->
                </div><!--w-view-->
            </vue2-gesture>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import FiltersForms from '@discover/views/widgets/FiltersForms.vue';
    import discoverModule from '@discover/module/store/type';

    export default {
        mixins: [require('@general/lib/mixin').default],
        components: {
            FiltersForms
        },
        data() {
            return {
                scroll: 0,
                show: true,
            }
        },
        methods: {
            applyFilters() {
                this.$store.dispatch(discoverModule.actions.users.setRefreshQueued, true)
                this.goTo('/discover')
            },
            checkScroll() {
                if (this.$refs.mobileScrollTopContainer) {
                    let currentScroll = this.$refs.mobileScrollTopContainer.scrollTop
                    if (currentScroll > this.scroll && currentScroll > 50 && this.show) {
                        this.show = false
                    } else if (currentScroll < this.scroll && !this.show || currentScroll <= 50) {
                        this.show = true
                    }

                    this.scroll = currentScroll
                }
            },
            handleGesture(str, e) {
                if (str == 'swipeRight') 
                    this.goBack()
            },
        },
        mounted() {
            let v = this
            if (app.isMobile && this.$refs && this.$refs.mobileScrollTopContainer) {
                this.$refs.mobileScrollTopContainer.addEventListener('scroll', function() {
                    v.checkScroll()
                })
            }
        },
    }
</script>
