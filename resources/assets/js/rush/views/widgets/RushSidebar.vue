<template>
    <div id="rushSidebar" :class="{'active': isSidebarActive, 'pushToBottom': toBottom}">
        <a href="/page/rush" class="sidebarLink sidebarLink-initial">What is Rush?</a>
        <a class="sidebarLink" @click="goToAdd">
            <svg class="icon"><use v-bind:xlink:href="getSvg('icon-plus')"></use></svg>
            New strip
        </a>
        <hr />
        <h2>My strips:</h2>
        <ul>
            <li v-if="myRushes.length > 0" v-for="rush in myRushes">
                <svg><use v-bind:xlink:href="getSvg('icon-pie-'+ rush.pie_part)"></use></svg>
                <router-link :to="{ name: 'rush.edit', params: {rushId: rush.id} }" @click.native="closeSidebar">
                    {{ rush.title }}
                </router-link>
            </li>
            <li v-else class="empty">You have no strips yet</li>
        </ul>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

import {
    _type as userType
} from '@rush/modules/user'

import {
    _type as sidebarType,
} from '@rush/modules/sidebar'

import {
    _type as requirementsType
} from '@rush/modules/requirements'

export default {
    props: ['isSidebarActive', 'myRushes'],
    mixins: [
        require('@rush/lib/mixin').default,
    ],
    computed: {
        ...mapGetters({
            userProfile: userType.getters.profile,
        }),
        toBottom() {
            return this.$route.name == 'rush.add' || this.$route.name == 'rush.edit'
        },
    },
    methods: {
        ...mapActions({
            closeSidebar:    sidebarType.actions.close,
            showRequirement: requirementsType.actions.show
        }),
        goToAdd() {
            this.closeSidebar()
            if (this.myRushes.length >= app.strips_limit && !this.userProfile.isPro) {
                this.showRequirement({
                    type:        'stars_and_strips',
                    title:       this.trans('pro_slides_4_title'),
                    description: this.trans('rush.stars_and_strips_upgrade'),
                    button:      this.trans('upgrade_now'),
                })
            } else if (this.$route.name != 'rush.add') {
                this.$router.push({name: 'rush.add'})
            }
        },
    },
}
</script>
