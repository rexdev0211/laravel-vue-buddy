<template>
    <div class="w-root">
        <div class="w-views">
            <div id="application-wrapper">
                <div class="content-wrapper">
                    <div class="conversation-screen">
                        <div id="conversation-group-scroll" class="conversation group" v-if="event">

                            <div class="header">
                                <i class="back" @click="goTo('/chat')"></i>

                                <div class="info group" @click="goToEvent"
                                    :class="{'online': event.isOnline, 'was-online': event.wasRecentlyOnline && !event.isOnline}">
                                    <div class="img" :style="{'background': `url(${event.photo_small}) no-repeat center / cover`}"></div>
                                    <div class="col">
                                        <div class="event-title">{{ event.type === 'club' ? event.title : trans('events.type.bang') }}</div>
                                        <div class="date" v-if="event.type !== 'club'">{{ event.date | formatDate('day-date') }}</div>
                                    </div>
                                </div>
                            </div>

                            <vue2-gesture :type="'swipeRight'" :call="handleGesture.bind(this, 'swipeRight')">
                                <GroupChatComponent chatMode="group" :eventId="parseInt(eventId)" v-if="event"></GroupChatComponent>
                            </vue2-gesture>
                        </div>
                    </div>
                </div><!--content-wrapper-->
            </div><!--#application-wrapper-->
        </div><!--w-views-->
    </div><!--w-root-->
</template>

<script>
    import { mapGetters, mapState } from 'vuex'
    import GroupChatComponent from '@chat/views/widgets/GroupChatComponent.vue';
    import eventsModule from '@events/module/store/type';

    export default {
        props: ['eventId'],
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-chat').default,
        ],
        data() {
            return {
            }
        },
        computed: {
            ...mapState({
                blockedUsersIds: state => state.blockedUsersIds,
            }),
            ...mapGetters({
                membershipRequests: eventsModule.getters.membershipRequests
            }),
            event(){
                return this.$store.getters.getEvent(this.eventId)
            }
        },
        watch: {
            blockedUsersIds: function(blockedUsersIds) {
                if (blockedUsersIds.includes(this.event?.user_id)) {
                    this.goTo('/chat')
                }
            }
        },
        components: {
            GroupChatComponent
        },
        methods: {
            goToEvent() {
                if (this.event.type === 'club') {
                    this.goTo('/club/' + this.eventId);
                } else {
                    this.goTo('/bang/' + this.eventId);
                }
            },
            handleGesture(str, e) {
                if (str == 'swipeRight') {
                    this.goTo('/chat')
                }
            },
        },
        mounted(){
            /*console.log('[ChatEventUser] mounted', {
                eventId: this.eventId
            })*/
            this.$store.dispatch(eventsModule.actions.events.loadInfo, this.eventId)
        }
    }
</script>