import eventsModule from '@events/module/store/type'
import chatModule from '@chat/module/store/type'

import {mapGetters, mapActions} from 'vuex';

export default {
    methods: {
        ...mapActions({
            toggleEventLike: eventsModule.actions.events.like,
            setBang: eventsModule.actions.setBang,
            requestMembership: eventsModule.actions.membership.request,
            showDialog: 'showDialog'
        }),
        updateMembership(payload){
            let membershipStatus = this.bang.membership || null
            console.log('[updateMembership]', {
                initialStatus: membershipStatus
            })

            let self = this
            switch (membershipStatus) {
                case null:
                case 'leaved':{
                    // Dialog - I want to take part. Send my application - YES / NO
                    // Your application has been sent
                    payload.action = 'request'
                    this.showDialog({
                        mode: 'confirm',
                        message: 'Send your application?',
                        callback: () => {
                            self.requestMembership(payload)
                        }
                    })

                    break
                }
                case 'host':{
                    // You are the host
                    return
                }
                case 'member':{
                    // I am a member already - LEAVE / STAY
                    // Dialog - Leave now? - NO / YES
                    // You have left this event
                    this.showDialog({
                        mode: 'confirm',
                        message: `Leave this bang?`,
                        callback: () => {
                            payload.action = 'leave'
                            self.requestMembership(payload)

                            this.closeBang();
                            setTimeout(function(){
                                app.$emit('reload-events')
                            }, 1500)
                        }
                    })
                    break
                }
                case 'requested':{
                    // Your application has been sent already
                    return
                }
                default:{
                    console.log('[updateMembership] Out of scope. Aborted.')
                    return
                }
            }
        },
        updateUserMembership(user, action) {
            let self = this
            let payload = {
                eventId: this.eventIdComputed,
                userId: user.id,
                action
            }

            if (action === 'accept') {
                this.showDialog({
                    mode: 'confirm-or-view-profile',
                    user:  user,
                    message: `Accept ${user.name} to the bang?`,
                    callback: () => {
                        self.requestMembership(payload)
                    },
                    callbackNegative: () => {
                        payload.action = 'reject'
                        self.requestMembership(payload)
                    },
                })

            } else if (action === 'remove') {
                this.showDialog({
                    mode: 'confirm',
                    message: `Remove ${user.name} from the bang?`,
                    callback: () => {
                        self.requestMembership(payload)
                    }
                })
            }
        }
    },
    computed: {
        ...mapGetters({
            bangData: eventsModule.getters.bang,
            unreadEventMessagesCount: chatModule.getters.messages.count.unreadEvent,
        }),
        membershipActionText(){
            let string = 'join'
            if (this.bang.membership === 'host') {
                string = 'host'
            } else if (this.bang.membership === 'member') {
                string = 'member'
            } else if (this.bang.membership === 'requested') {
                string = 'pending'
            } else if (
                this.bang.membership === 'removed'
                ||
                this.bang.membership === 'rejected'
            ) {
                string = 'declined'
            } else if (this.bang.membership === null) {
                string = 'join'
            }
            return this.trans(string)
        },
        membershipActionBtnClass(){
            if (this.bang.membership === 'member') {
                return 'btn'
            }
            if (this.bang.membership === 'host') {
                return ['btn', 'bb-button-no_pointer']
            }
            return 'btn'
        },
        membershipActionDisabled(){
            return ['requested', 'rejected'].includes(this.bang.membership)
        },
    }
}
