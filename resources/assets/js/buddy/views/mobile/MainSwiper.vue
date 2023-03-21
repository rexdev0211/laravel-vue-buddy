<template>
    <!-- Slider main container -->
    <div class="w-swiper-full swiper-container">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
            <!-- Slides -->
            <div class="swiper-slide">
                <Discover></Discover>
            </div>
            <div class="swiper-slide">
                <Chat></Chat>
            </div>
            <div class="swiper-slide">
                <transition :name="profileTransitionName" mode="out-in">
                    <component :is="profileComponent" class="child-view"></component>
                </transition>
            </div>
        </div>
    </div>
</template>

<script>
    import Discover from '@discover/views/mobile/Discover.vue';
    import Chat from '@chat/views/mobile/Conversations.vue';

    import ProfileInfo from '@profile/views/mobile/page/ProfileInfo.vue';
    import ProfileEdit from '@profile/views/mobile/page/ProfileEdit.vue';
    import ProfileSettings from '@profile/views/mobile/page/ProfileSettings.vue';
    import ProfilePhotos from '@profile/views/mobile/page/ProfilePhotos.vue';

    export default {
        data() {
            return {
                profileComponent: '',
                profileComponents: [
                    { path: '/profile/edit', name: 'ProfileEdit'},
                    { path: '/profile/settings', name: 'ProfileSettings'},
                    { path: '/profile/photos', name: 'ProfilePhotos'}
                ],
                profileTransitionName: 'slide-right'
            }
        },
        mixins: [require('@general/lib/mixin').default],
        components: {
            Discover,
            Chat,
            ProfileInfo,
            ProfileSettings,
            ProfilePhotos,
            ProfileEdit
        },
        //this is for loading correct component when navigating inside MainSwiper child components
        watch: {
            '$route' (to, from) {
                this.matchProfileComponentToCurrentUrl();
                mySwiper.slideTo(this.getTabIndex());

                let tabs = ['profile', 'profileStats', 'profilePhotos', 'profileSettings'];
                if (tabs.indexOf(from.name) < tabs.indexOf(to.name)) {
                    this.profileTransitionName = 'slide-left';
                } else {
                    this.profileTransitionName = 'slide-right';
                }
            }
        },
        methods: {
            getTabIndex() {
                let pathName = window.location.pathname;

                if(pathName == '/discover') return 0;
                else if (pathName == '/chat') return 1;
                else return 2;
            },
            getPathByComponentName(componentName) {
                return this.profileComponents.find(el => el.name == componentName).path;
            },
            matchProfileComponentToCurrentUrl() {
                let pathName = window.location.pathname;

                let currentComponent = this.profileComponents.find(el => el.path == pathName);

                if(currentComponent === undefined) {
                    currentComponent = this.profileComponents[0];
                }

                this.profileComponent = currentComponent.name;
            },
            mainSlideChanged(newIndex) {
                if (newIndex == 0) this.$router.push('/discover');
                else if (newIndex == 1) this.$router.push('/chat');
                else this.$router.push(this.getPathByComponentName(this.profileComponent));
            }
        },
        mounted() {
            this.matchProfileComponentToCurrentUrl();

            this.loadSwiper(this.getTabIndex(), this.mainSlideChanged);

            app.$on('ProfileMenuTabChanged', this.matchProfileComponentToCurrentUrl);
        },
        beforeDestroy() {
            app.$off('ProfileMenuTabChanged');
        }
    }
</script>