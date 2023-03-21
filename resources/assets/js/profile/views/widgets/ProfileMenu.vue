<template>
    <vue-custom-scrollbar class="user-settings">
        <div class="inner">
            <div class="top" v-if="profile.id">
                <div id="add-pics" class="user-box" v-if="isMobile"
                    @click.prevent="goTo('/profile/edit')"
                    :class="{'online': !discreetModeEnabled}">
                    <div class="img" :style="{'background': `url(${defaultPhoto}) no-repeat center / cover`}"></div>
                    <div class="edit"></div>
                </div>
                <div id="add-pics" class="user-box" v-else
                    @click.prevent="openProfileEdit"
                    :class="{'online': !discreetModeEnabled}">
                    <div class="img" :style="{'background': `url(${defaultPhoto}) no-repeat center / cover`}"></div>
                    <div class="edit"></div>
                </div>
                <div class="buttons">
                    <div class="button mask" id="mask-mode"
                        :class="{'active': discreetModeEnabled}"
                        @click="trySwitchDiscreetMode">
                        <span>{{ trans('discreet_mode') }}</span>
                    </div>
                </div>
            </div>
            <div class="profile-edit">
                <router-link class="btn" :to="`/profile/edit`" v-if="isMobile">
                    <span>{{ 'Edit Profile' || trans('edit_profile') }}</span>
                </router-link>
                <a id="edit-profile-menu" class="btn" v-else
                    @click.prevent="openProfileEdit">
                    {{ 'Edit Profile' || trans('edit_profile') }}
                </a>
            </div>
            <div class="menu">
                <ul>
                    <li class="location notranslate">
                        <router-link class="profile-menu-item_link" v-if="isMobile"
                            :to="'/profile/location'">
                            <span class="menu-item-button location"></span>
                            <span class="menu-item-label">{{ this.profile.address || trans('location') }}</span>
                        </router-link>
                        <a class="profile-menu-item_link" v-else
                            @click.prevent="openProfileLocation">
                            <span class="menu-item-button location"></span>
                            <span class="menu-item-label">{{ this.profile.address || trans('location') }}</span>
                        </a>
                        <span class="location-refresh"
                            @click="forceUpdateLocation">
                            <i class="refresh" v-show="!locationUpdating"></i>
                            <img v-show="locationUpdating" class="preloader"
                                src="/assets/img/preloader.svg"
                                alt="">
                        </span>
                    </li>
                    <li>
                        <router-link class="profile-menu-item_link" v-if="isMobile"
                            :to="'/profile/photos'">
                            <span class="menu-item-button photos"></span>
                            <span class="menu-item-label">{{ trans('photos') }}</span>
                        </router-link>
                        <a class="profile-menu-item_link" v-else
                            @click.prevent="openProfilePhotos">
                            <span class="menu-item-button photos"></span>
                            <span class="menu-item-label">{{ trans('photos') }}</span>
                        </a>
                    </li>
                    <li>
                        <router-link class="profile-menu-item_link" v-if="isMobile"
                            :to="'/profile/videos'">
                            <span class="menu-item-button videos"></span>
                            <span class="menu-item-label">{{ trans('videos') }}</span>
                        </router-link>
                        <a class="profile-menu-item_link" v-else
                            @click.prevent="openProfileVideos">
                            <span class="menu-item-button videos"></span>
                            <span class="menu-item-label">{{ trans('videos') }}</span>
                        </a>
                    </li>
                    <li>
                        <router-link class="profile-menu-item_link" v-if="isMobile"
                            :to="'/profile/settings'">
                            <span class="menu-item-button settings"></span>
                            <span class="menu-item-label">{{ trans('settings') }}</span>
                        </router-link>
                        <a class="profile-menu-item_link" v-else
                            @click.prevent="openProfileSettings">
                            <span class="menu-item-button settings"></span>
                            <span class="menu-item-label">{{ trans('settings') }}</span>
                        </a>
                    </li>
                    <li class="pro">
                        <div class="profile-menu-item_link" @click="goPro">
                            <span class="menu-item-button pro"></span>
                            <span class="menu-item-label">{{ trans('upgrade_to_pro') }}</span>
                        </div>
                    </li>
                    <li>
                        <router-link class="profile-menu-item_link" v-if="isMobile"
                                     :to="'/profile/share'">
                          <span class="menu-item-button share"></span>
                          <span class="menu-item-label">{{ trans('share_buddy_app') }}</span>
                        </router-link>
                        <a class="profile-menu-item_link" v-else
                           @click.prevent="openProfileShare">
                          <span class="menu-item-button share"></span>
                          <span class="menu-item-label">{{ trans('share_buddy_app') }}</span>
                        </a>
                    </li>
                    <li>
                        <router-link class="profile-menu-item_link" v-if="isMobile"
                            :to="'/profile/help'">
                            <span class="menu-item-button help"></span>
                            <span class="menu-item-label">{{ trans('help') }}</span>
                        </router-link>
                        <a class="profile-menu-item_link" v-else
                            @click.prevent="openProfileHelp">
                            <span class="menu-item-button help"></span>
                            <span class="menu-item-label">{{ trans('help') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="profile-menu-item_link"
                            @click="logout">
                            <span class="menu-item-button logout"></span>
                            <span class="menu-item-label">{{ trans('logout') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </vue-custom-scrollbar>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        props: ['tab'],
        mixins: [
            require('@general/lib/mixin').default
        ],
        data() {
            return {

            }
        },
        components: {
          vueCustomScrollbar
        },
        methods: {
            ...mapActions([
                'trySwitchDiscreetMode',
                'forceUpdateLocation',
                'openProfileEdit',
                'openProfileLocation',
                'openProfilePhotos',
                'openProfileVideos',
                'openProfileSettings',
                'openProfileShare',
                'openProfileHelp',
                'closeAllProfilePages'
            ]),
            goPro() {
              let value = !this.sidebar.profile.visible
              this.$store.dispatch('updateSidebarVisibility', {index: 'profile', value})
              this.closeAllProfilePages()
              this.goTo('/profile/pro')
            }
        },
        computed: {
            ...mapGetters([
                'discreetModeEnabled'
            ]),
            ...mapState([
                'userIsPro',
                'profilePhotos',
                'profile',
                'locationUpdating',
                'sidebar'
            ]),
            defaultPhoto() {
                let avatars = this.profile.avatars
                if (!avatars.adult && (avatars.default?.rejected || avatars.default?.pending)) {
                  return '/assets/img/default_180x180.jpg';
                } else {
                  return _.get(avatars, 'merged.photo_small', '/assets/img/default_180x180.jpg')
                }
            }
        }
    }
</script>
<style lang="scss">
.pro {
  .profile-menu-item_link {
    align-items: center !important;
    cursor: pointer;
  }
}
</style>