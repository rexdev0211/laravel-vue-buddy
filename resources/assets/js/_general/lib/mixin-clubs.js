import {mapGetters, mapActions} from 'vuex';

import clubsModule from '@clubs/module/store/type'

export default {
    computed: {
        ...mapGetters({
            membershipRequests: clubsModule.getters.membershipRequests
        }),
        membershipActionText(){
            let string = 'join'
            if (this.club.membership === 'host') {
                string = 'owner'
            } else if (this.club.membership === 'member') {
                string = 'member'
            } else if (this.club.membership === 'requested') {
                string = 'pending'
            } else if (
                this.club.membership === 'removed'
                ||
                this.club.membership === 'rejected'
            ) {
                string = 'declined'
            } else if (this.club.membership === null) {
                string = 'join'
            }
            return this.trans(string)
        },
        membershipActionBtnClass(){
            if (this.club.membership === 'member') {
                return 'btn'
            }
            if (this.club.membership === 'host') {
                return ['btn', 'bb-button-no_pointer']
            }
            if (this.club.membership === 'rejected' || this.club.membership === 'removed') {
                return ['btn', 'darker']
            }
            return 'btn'
        },
        membershipActionDisabled(){
            return ['requested'].includes(this.club.membership)
        },
    },
    methods: {
        ...mapActions({
            requestMembership: clubsModule.actions.membership.request,
            requestMembershipAll: clubsModule.actions.membership.requestAll,
            requirementsAlertShow: 'requirementsAlertShow',
            showDialog: 'showDialog'
        }),
        updateMembership(payload){
            let membershipStatus = this.club.membership || null
            console.log('[updateMembership]', {
                initialStatus: membershipStatus
            })

            let self = this
            switch (membershipStatus) {
                case null:
                case 'leaved':
                case 'rejected':
                case 'removed':{
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
                        message: `Leave this club?`,
                        callback: () => {
                            payload.action = 'leave'
                            self.requestMembership(payload)

                            this.closeClub();
                            setTimeout(function(){
                                app.$emit('reload-clubs')
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
                eventId: this.clubIdComputed,
                userId: user.id,
                action
            }

            if (action === 'accept') {
                this.showDialog({
                    mode: 'confirm-or-view-profile-club',
                    user:  user,
                    message: `Accept ${user.name} to the club?`,
                    callback: (mode = '') => {
                        if (mode === 'accept_all_for_clubs') {
                            payload.userId = -1;
                            self.requestMembershipAll(payload)
                        } else {
                            self.requestMembership(payload)
                        }
                    },
                    callbackNegative: () => {
                        payload.action = 'reject'
                        self.requestMembership(payload)
                    },
                })

            } else if (action === 'remove') {
                this.showDialog({
                    mode: 'confirm',
                    message: `Remove ${user.name} from the club?`,
                    callback: () => {
                        self.requestMembership(payload)
                    }
                })
            }
        },
        createClub() {
            if (this.isDesktop) {
                this.openClubModal(null, 'create')
            } else {
                this.$router.push({ name: 'create-club' })
            }
        },
        editClub(clubId){
            console.log('editClub', { clubId })
            if (this.isDesktop) {
                this.openClubModal(clubId, 'edit')
            } else {
                this.$router.push({
                    name: 'edit-club',
                    params: { clubId }
                })
            }
        },
        openEvent(eventId, type) {
            let entityType = type === 'bang' ? 'bang' : 'event'
            console.log('openEvent', { entityType, eventId })
            if (this.isDesktop) {
                if (this.$route.name !== 'clubs') {
                    this.$router.push({name:'clubs'})
                }
                this.openClubModal(eventId, entityType)
            } else {
                this.$router.push({
                    name: entityType,
                    params: { eventId }
                })
            }
            this.$store.commit('updateMyEvent', {
                id: eventId,
                is_new: false
            })
        },
        openClub(clubId) {
            console.log('openClub', { clubId })
            if (this.isDesktop) {
                if (this.$route.name !== 'clubs') {
                    this.$router.push({name:'clubs'})
                }
                this.openClubModal(clubId)
            } else {
                this.$router.push({
                    name: 'club',
                    params: { clubId }
                })
            }
            this.$store.commit('updateMyClub', {
                id: clubId,
                is_new: false
            })
        },
        async openClubModal(clubId, mode) {
            this.$store.dispatch(clubsModule.actions.setClub, { visible: false });
            if (mode !== 'create') {
                await this.$store.dispatch(clubsModule.actions.clubs.loadInfo, clubId)
            }

            let payload = {
                visible: true,
                clubId: clubId,
                mode: mode || 'view'
            }
            console.log('[openClubModal]', payload)

            this.$store.dispatch(clubsModule.actions.setClub, payload)
        },
        closeEvent() {
            this.$store.dispatch(clubsModule.actions.setEvent, { visible: false })
        },
        closeClub() {
            this.$store.dispatch(clubsModule.actions.setClub, { visible: false })
        },
        async handleEventInvitation(type, data)
        {
            return await axios.post('/api/handle-invitation/'+type, data);
        },
        acceptEventInvitation(data)
        {
            return this.handleEventInvitation('accept', data);
        },
        declineEventInvitation(data)
        {
            return this.handleEventInvitation('decline', data);
        },
        resetEventsAround() {
            console.log('[Events] Reset')

            this.$store.commit(clubsModule.mutations.clubs.set, []);
            this.$store.commit(clubsModule.mutations.resetPagination)

            app.lastEventsAroundRefresh = moment();

            //otherwise it would scroll to the bottom when you change filter
            $('#js-clubs-content').animate({scrollTop: 0}, 0);

            if (this.$refs.infiniteLoadingEvents) {
                this.$refs.infiniteLoadingEvents.stateChanger.reset()
            } else {
                console.log('No infiniteLoadingEvents ref found')
            }
        },
        resetClubsAround() {
            console.log('[Clubs] Reset')

            this.$store.commit(clubsModule.mutations.clubs.set, []);
            this.$store.commit(clubsModule.mutations.resetPagination)

            app.lastEventsAroundRefresh = moment();

            //otherwise it would scroll to the bottom when you change filter
            $('#js-clubs-content').animate({scrollTop: 0}, 0);

            if (this.$refs.infiniteLoadingClubs) {
                this.$refs.infiniteLoadingClubs.stateChanger.reset()
            } else {
                console.log('No infiniteLoadingClubs ref found')
            }
        },
        async loadClubs(infiniteScroll) {
            let datesCount = await this.$store.dispatch(clubsModule.actions.clubs.load)
            if (datesCount) {
                infiniteScroll.loaded()
            }
            if (datesCount < window.LOAD_CLUBS_PER_PAGE) {
                infiniteScroll.complete()
            }
        },
    }
}
