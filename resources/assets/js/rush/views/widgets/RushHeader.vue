<template>
    <div id="rushHeader" :class="{'noLinks': !linksAllowed, 'hidden': !isHeaderAllowed}">
        <div class="rushHeaderLogo" @click="hideWidget"></div>

        <div class="rushEditStreak" v-if="streak != null" @click="showStreakTooltip">
            <svg class="icon"><use v-bind:xlink:href="getSvg('icon-bunny')"></use></svg>
            {{ streak }}
            <div class="tooltip tooltip-bottom-left" v-if="isStreakTooltipVisible">
                {{ trans('rush.running_for_x_days', {days: streak}) }}
            </div>
        </div>

        <ul class="rushHeaderLinks">
            <li @click="hideWidget">
                <a href="/discover">
                    <svg class="icon"><use v-bind:xlink:href="getSvg('icon-radar')"></use></svg>
                </a>
            </li>
            <li @click="hideWidget">
                <a href="/events">
                    <svg class="icon"><use v-bind:xlink:href="getSvg('icon-calendar_main')"></use></svg>
                </a>
            </li>
            <li id="rushChat" @click="hideWidget">
                <a href="/chat">
                    <svg class="icon"><use v-bind:xlink:href="getSvg('icon-message_main')"></use></svg>
                    <span class="incoming" v-if="userNotifications.messages == true"></span>
                </a>
            </li>
            <li @click="hideWidget">
                <a href="/notifications">
                    <svg class="icon"><use v-bind:xlink:href="getSvg('icon-notification')"></use></svg>
                    <span class="incoming" v-if="userNotifications.notifications == true"></span>
                </a>
            </li>
            <li class="widgetButtonHolder">
                <span class="widgetButton">
                    <a class="widgetIcon" @click="widgetToggle">
                        <svg v-if="$route.name == 'rush'" class="icon"><use v-bind:xlink:href="getSvg('icon-widget_active')"></use></svg>
                        <svg v-else class="icon"><use v-bind:xlink:href="getSvg('icon-widget')"></use></svg>
                    </a>
                    <div class="widgetHolder" :style="{'display': widget ? 'block' : ''}">
                        <div class="widgetHeader">
                            <svg class="icon icon-widget"><use v-bind:xlink:href="getSvg('icon-widget_active')"></use></svg>
                            <i>Widgets</i>
                        </div>
                        <div class="widgetLinks">
                            <a @click="redirectToList">
                                <img src="/images/rush/svg/rush_logo.svg" alt="Rush" />
                                <i>Rush</i>
                            </a>
                        </div>
                    </div>
                </span>

            </li>
        </ul>

        <a href="/profile/edit" class="rushHeaderProfileLink" @click="hideWidget">
            <svg v-if="userProfile && userProfile.discreet_mode" class="icon icon-discreet_on"><use v-bind:xlink:href="getSvg('icon-discreet_on')"></use></svg>
            <svg v-else class="icon icon-profile"><use v-bind:xlink:href="getSvg('icon-profile')"></use></svg>
        </a>

        <div class="rushHeaderFavorites" @click="closeOverlays">
            <div v-for="rush in rushFavorites" class="rush" @click="openRush(rush.id)" :style="{'background-image': rush.latest_strip.type == 'image' ? 'url(\''+ rush.latest_strip.image + '\')' : false}">
                <span v-if="rush.have_unviewed">{{ rush.count_unviewed }}</span>
            </div>
        </div>
        <div class="rushHeaderMenu">
            <svg @click="openSidebarHideWidget" v-if="!isSidebarActive"><use v-bind:xlink:href="getSvg('icon-hamburger')"></use></svg>
            <svg class="icon-close" @click="closeOverlays" v-else><use v-bind:xlink:href="getSvg('icon-close')"></use></svg>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

import {
    _type as userType,
} from '@rush/modules/user'

import {
    _type as sidebarType,
} from '@rush/modules/sidebar'

import {
    _type as headerType,
} from '@rush/modules/header'

export default {
    data() {
        return  {
            widgetInitiated: false,
            hideStreakTooltipTimeout: null,
            isStreakTooltipVisible: false,
        }
    },
    props: ['isHeaderAllowed'],
    mixins: [
        require('@rush/lib/mixin').default,
    ],
    computed: {
        ...mapGetters({
            userProfile:       userType.getters.profile,
            userNotifications: userType.getters.notifications,
            rushFavorites:     userType.getters.rush.favorites,
            isSidebarActive:   sidebarType.getters.active,
            streak:            headerType.getters.streak,
            widget:            headerType.getters.widget,
        }),
        linksAllowed() {
            return this.$route.name != 'rush.add' && this.$route.name != 'rush.edit'
        },
    },
    methods: {
        ...mapActions({
            openSidebar:  sidebarType.actions.open,
            closeSidebar: sidebarType.actions.close,
            showWidget:   headerType.actions.widget.show,
            hideWidget:   headerType.actions.widget.hide,
        }),
        showStreakTooltip() {
            this.closeSidebar()
            this.isStreakTooltipVisible = true

            clearTimeout(v.hideStreakTooltipTimeout)

            let v = this
            this.hideStreakTooltipTimeout = setTimeout(() => {
                v.isStreakTooltipVisible = false
            }, 2000)
        },
        openRush(rushId) {
            this.$router.push({name: 'rush.favorite', params: {rushId: rushId}})
        },
        redirectToList() {
            if (this.$route.name != 'rush')
                this.$router.push({name: 'rush'})

            this.hideWidget()
        },
        widgetToggle() {
            if (this.widget) this.hideWidget()
            else this.showWidget()
        },
        openSidebarHideWidget() {
            this.openSidebar()
            this.hideWidget()
        },
        closeOverlays() {
            this.closeSidebar()
            this.hideWidget()
        },
    },
    watch: {
        userProfile() {
            if (!this.widgetInitiated) {
                this.widgetInitiated = true
                if (localStorage.getItem('widgetOpen')) {
                    this.widgetToggle()
                    localStorage.removeItem('widgetOpen')
                }
            }
        }
    }
}
</script>
