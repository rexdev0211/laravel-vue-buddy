<template>
    <div class="w-views page-bang">
        <div class="content-wrapper">
            <div class="event-screen">
                <div class="event-details bang">
                    <div class="header">
                        <i class="back" @click="goBack()"></i>
                        <i class="dots" v-if="club && club.membership !== 'host'" v-show="!reportMenuVisible" v-on:click="showReportMenu"></i>
                    </div>

                    <ClubDetails :clubId="clubId"/>

                    <div class="mobileSidebarHolder positionRight"
                        v-show="showReportMenu"
                        :class="{'active': reportMenuVisible}">
                        <div class="mobileSidebarHide" @click="hideReportMenu"></div>
                        <div class="mobileSidebar">

                            <div class="report-menu">
                                <div class="inner">
                                    <div class="title">{{ trans('clubs.report.title') }}</div>
                                    <div class="box">
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="wrong-type-club" value="wrong_category" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('clubs.report.wrong_type') }}</div>
                                            </label>
                                        </div>
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="under-age" value="under_age" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('report_under_age') }}</div>
                                            </label>
                                        </div>
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="illegal-club" value="illegal" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('clubs.report.illegal') }}</div>
                                            </label>
                                        </div>
                                        <div class="checkbox-container">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="spam-club" value="spam" v-model="reportData">
                                                <span class="checkbox-custom"></span>
                                                <div class="input-title">{{ trans('report_spam') }}</div>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn" @click="sendClubReport">{{ trans('send') }}</button>
                                </div>
                            </div><!--report-menu-->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters, mapState} from 'vuex';

    import ClubDetails from "@clubs/views/widgets/Item.vue";
    import clubsModule from '@clubs/module/store/type'
    import chatModule from '@chat/module/store/type'

    export default {
        components: {
            ClubDetails
        },
        data() {
            return {
                menuVisible: false,
                reportMenuVisible: false,
                reportData: []
            }
        },
        props: ['clubId'],
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-clubs').default,
            // require('@general/lib/mixin-bang').default,
        ],
        methods: {
            ...mapActions({
                setClub: clubsModule.actions.setClub,
                reportClub: clubsModule.actions.clubs.report,
                toggleClubLike: clubsModule.actions.clubs.like
            }),
            showReportMenu() {
                this.reportMenuVisible = true;
            },
            hideReportMenu() {
                this.reportMenuVisible = false;
            },
            closeFilter() {
                this.hideReportMenu();
            },
            sendClubReport() {
              this.reportClub({
                reason: this.reportData,
                id: this.clubId,
                callback: this.clubReportResponse,
              })
            },
            clubReportResponse(response) {
                if (response.data.success) {
                    this.showSuccessNotification(response.data.trans)
                } else {
                    this.showErrorNotification(response.data.trans)
                }
                this.closeFilter()
            },
            async chatBang() {
                if (!this.isMyClub(this.club)) {
                    this.goTo(`/chat/bang/${this.clubId}`);
                } else {
                    this.goTo('/chat');
                }
            },
        },
        computed: {
            ...mapState({
                blockedUsersIds: state => state.blockedUsersIds,
            }),
            ...mapGetters({
                unreadClubMessagesCount: chatModule.getters.messages.count.unreadClub
            }),
            heartFilledIn() {
                return this.club.isLiked
            },
            club() {
                return this.$store.getters.getClub(this.clubId)
            },
        },
        watch: {
            blockedUsersIds: function(blockedUsersIds) {
                if (blockedUsersIds.includes(this.club?.user_id)) {
                    this.goTo('/clubs')
                }
            }
        },
        beforeMount() {
            this.$store.dispatch(clubsModule.actions.clubs.loadInfo, this.clubId)
        },
        activated() {
              console.log('[Club Mobile Item] Activated')
            //   this.$store.commit('updateUser', { has_club_notifications: false })
          }
    }
</script>
