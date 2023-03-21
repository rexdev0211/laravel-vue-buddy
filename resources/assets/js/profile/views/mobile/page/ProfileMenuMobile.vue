<template>
    <div class="w-root">
        <div class="w-views">
            <vue2-gesture :type="'swipeLeft'" :call="handleGesture.bind(this, 'swipeLeft')">
                <div class="w-view">
                    <transition :name="'fade'" mode="out-in">
                        <div v-if="show" class="w-navbar">
                            <div class="b-navbar centered">
                                <div class="navbar-left"></div>
                                <div class="text" v-bind:class="{'text--icon': userIsPro}">
                                    <svg class="icon icon-settings_active" v-if="userIsPro"><use v-bind:xlink:href="symbolsSvgUrl('pro')"></use></svg>
                                    {{ profile.name }}
                                </div>
                                <div class="navbar-right"></div>
                            </div>
                        </div>
                    </transition>

                    <div class="w-pages">
                        <div class="b-page">
                            <div class="w-page__content" ref="mobileScrollTopContainer">
                                <div class="row row-profile-menu">
                                    <ProfileMenu/>
                                </div><!--row-->
                            </div><!--b-login__page-->
                        </div><!--b-page-->
                    </div><!--w-pages-->

                    <BottomBar tab="profile"/>
                </div><!--w-view-->
            </vue2-gesture>
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import ProfileMenu from "@profile/views/widgets/ProfileMenu.vue";
    import BottomBar from '@buddy/views/widgets/BottomBar.vue';
    import {mapActions, mapGetters, mapState} from 'vuex';

    export default {
        mixins: [
            require('@general/lib/mixin').default
        ],
        components: {
            ProfileMenu,
            BottomBar,
        },
        data() {
            return {
                scroll: 0,
                show: true,
            }
        },
        methods: {
            ...mapActions([
                'trySwitchDiscreetMode',
            ]),
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
                if (str == 'swipeLeft') 
                    this.goBack()
            },
        },
        computed: {
            ...mapState({
                userIsPro: 'userIsPro',
                profile:   'profile',
            }),
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
