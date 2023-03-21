<template>
    <div class="box">
        <div class="tabs visits-tabs">
            <div class="tab visitors" dusk="visitors-tab" v-if="isMobile"
                :class="{'active': tab === 'visitors'}" @click="goTo('/visitors')">
                <div class="notificated" v-if="userHasNewVisitors"></div>
                <span>{{ trans('visitors') }}</span>
            </div>
            <div class="tab visitors" dusk="visitors-tab" v-else
                :class="{'active': tab === 'visitors'}" @click="setTab('visitors')">
                <div class="notificated" v-if="userHasNewVisitors"></div>
                <span>{{ trans('visitors') }}</span>
            </div>
            <div class="tab taps middle" dusk="notifications-tab" v-if="isMobile"
                :class="{'active': tab === 'notifications'}" @click="goTo('/notifications')">
                <span>{{ trans('taps') }}</span>
            </div>
            <div class="tab taps middle" dusk="notifications-tab" v-else
                :class="{'active': tab === 'notifications'}" @click="setTab('notifications')">
                <span>{{ trans('taps') }}</span>
            </div>
            <div class="tab visited last" dusk="visited-tab" v-if="isMobile"
                :class="{'active': tab === 'visited'}" @click="goTo('/visited')">
                <span>{{ trans('visited') }}</span>
            </div>
            <div class="tab visited last" dusk="visited-tab" v-else
                :class="{'active': tab === 'visited'}" @click="setTab('visited')">
                <span>{{ trans('visited') }}</span>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapState} from 'vuex';
    import notificationsModule from '@notifications/module/store/type';

    export default {
        props: ['tab', 'setTab', 'reset'],
        mixins: [require('@general/lib/mixin').default],
        computed: {
            ...mapGetters([
                'userHasNewNotifications',
                'userHasNewVisitors',
            ]),
            ...mapState({
                notifications: state => state.notificationsModule.notifications,
                visitors: state => state.notificationsModule.visitors,
                visited: state => state.notificationsModule.visited
            }),
        },
        created(){
            this.reset()
        },
        mounted() {
            this.setTab(this.tab)
        },
    }
</script>