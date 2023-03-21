<template>
    <div>
        <div class="w-app">
            <div id="application-wrapper">

                <TopBarDesktop/>

                <div class="content-wrapper">
                    <div class="calendar-menu" v-if="myClubs.length" :class="{'opened': myClubsMenuVisible}">
                        <div class="minimise" @click="myClubsMenuVisible = !myClubsMenuVisible"></div>
                        <div class="inner">
                            <div class="box">
                                <div class="headline">{{ trans('clubs.my_clubs') }}</div>
                                <div class="clubs">
                                    <div class="club" v-for="club in myClubs" :data-club="club.membership"
                                        @click="openClub(club.id, club.type)"
                                        :class="{'notificated': membershipRequests(club.id), 'new-club-entry': club.is_new || false}">
                                        <div class="img" :style="{'background': `url(${club.photo_small}) no-repeat center / cover`}">
                                          <div v-if="club.type === 'guide'"
                                               :class="{'pending': club.status === 'pending', 'rejected': club.status === 'declined'}"
                                          >
                                          </div>
                                        </div>
                                        <div class="details" v-if="club.type === 'bang'">
                                            <div class="status" v-if="club.membership === 'host'">{{ trans('host') }}</div>
                                            <div class="status" v-else-if="club.membership === 'member'">{{ trans('member') }}</div>
                                            <div class="status"  v-else-if="club.membership === 'requested'">{{ trans('pending') }}</div>
                                            <div class="location notranslate" v-if="club.locality"><span>{{ club.locality }}</span></div>
                                            <div class="date" v-if="club.date"><span>{{ club.date | formatDate('day-months-year') }}</span></div>
                                        </div>
                                        <div class="details" v-else-if="club.type === 'guide'">
                                            <div class="club-title">{{ club.title }}</div>
                                            <div class="location notranslate" v-if="club.locality"><span>{{ club.locality }}</span></div>
                                            <div class="date" v-if="club.date"><span>{{ club.date | formatDate('day-months-year') }}</span></div>
                                        </div>
                                        <div class="details" v-else>
                                            <div class="club-title">{{ club.title }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <vue-custom-scrollbar id="js-clubs-content" ref="vueCustomScrollbar" class="col calendar-list">
                        <div class="center">
                            <div class="tabs calendar-tabs">
                                <div :class="{'tab':true, 'active': type === typeItem}"
                                    v-for="typeItem in types"
                                    @click="setType(typeItem)">
                                    <span :class="{'notificated-bang':(typeItem === 'bang' && getInvitationsToClub.length && type !== typeItem)}">{{ trans(`clubs.type.${typeItem}`) }}</span>
                                </div>
                            </div>

                            <div class="tab-content-wrapper calendar-tabs" :class="{[type]: true}">
                                <div class="tab-content-inner">
                                    <div class="tab-content">
                                        <div class="clubs">

                                            <!-- <StickyClubsList :list="stickyClubs" :type="type"/> -->
                                            <InnerList/>

                                        </div>

                                        <div id="add-club" class="add-club-button"
                                            @click="createClub('club')" :title="trans('clubs.new_club')">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </vue-custom-scrollbar>
                </div>
                <HideChats />
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.notificated-bang {
  &:before {
    content: "";
    position: absolute;
    top: 15px;
    margin-left:-13px;
    z-index: 1;
    width: 10px;
    height: 10px;
    border-radius: 2px;
    background: #FF0000;
  }
}
</style>

<script>
    import InfiniteLoading from 'vue-infinite-loading';
    import {mapState, mapActions, mapGetters} from 'vuex';

    import TopBarDesktop from '@buddy/views/widgets/TopBarDesktop.vue';
    // import StickyClubsList from '@clubs/views/widgets/StickyClubsList.vue';
    import InnerList from '@clubs/views/widgets/InnerList.vue';
    // import HealthAlert from '@buddy/views/widgets/HealthAlert.vue';

    import clubsModule from '@clubs/module/store/type'
    import HideChats from "@buddy/views/widgets/HideChats.vue";

    // custom scrollbar
    import vueCustomScrollbar from 'vue-custom-scrollbar'
    import "vue-custom-scrollbar/dist/vueScrollbar.css"

    export default {
        mixins: [
            require('@general/lib/mixin').default,
            require('@general/lib/mixin-clubs').default,
        ],
        data() {
            return {
                types: [
                    'guide',
                    'fun',
                    'bang'
                ],
                myClubsMenuVisible: false
            }
        },
        computed: {
            ...mapState({
                myClubs: 'myClubs',
                userIsPro: 'userIsPro',
            }),
            ...mapGetters({
                type: clubsModule.getters.type,
                stickyClubs: clubsModule.getters.stickyClubs,
                clubDates: clubsModule.getters.clubDates,
                getInvitationsToClub:'getInvitationsToClub',
            }),
        },
        components: {
            InfiniteLoading,
            TopBarDesktop,
            InnerList,
            // StickyClubsList,
            HideChats,
            vueCustomScrollbar
            // HealthAlert
        },
        methods: {
            ...mapActions({
                requirementsAlertShow: 'requirementsAlertShow',
                setBang: clubsModule.actions.setBang,
                setClub: clubsModule.actions.setClub,
                getStickyClubs: clubsModule.actions.getStickyClubs,
            }),
            setType(type) {
                console.log('[Clubs List] setType')
                this.$store.dispatch(clubsModule.actions.setType, type)

                this.$store.dispatch(clubsModule.actions.setBang, { visible: false });
                this.$store.dispatch(clubsModule.actions.setClub, { visible: false });

                app.$emit('reload-clubs')
                // this.$store.dispatch('showHealthAlert')
            },
            createClub(type) {
                if (!this.userIsPro && this.myClubs.length >= window.FREE_EVENTS_LIMIT) {
                    this.requirementsAlertShow('clubs')
                } else {
                    let payload = { visible: true, mode: 'create' }
                    if (type === 'bang') {
                        this.setBang(payload)
                    } else {
                        this.setClub(payload)
                    }
                }
            }
        },
        mounted() {
            console.log('[Clubs] Mounted')
            let clubId = this.$route.query.clubId
            let bangId = this.$route.query.bangId
            if (clubId) {
                this.openClub(clubId, 'club')
            }
            if (bangId) {
                this.openClub(bangId, 'bang')
            }
            this.getStickyClubs()

            this.loadScrollTopButton('js-clubs-content');
        },
        deactivated() {
            console.log('[Clubs] Destroyed')
            this.$store.dispatch(clubsModule.actions.setBang, { visible: false });
            this.$store.dispatch(clubsModule.actions.setClub, { visible: false });
            app.$off('reload-clubs', this.resetClubsAround)
        },
        watch: {
          clubDates() {
              this.$refs.vueCustomScrollbar.$forceUpdate()
          }
        }
    }
</script>
