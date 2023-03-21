import clubsModule from "./type";
import { clubPhotosSort } from '@buddy/lib/helpers'
import Vue from "vue";

const mutations = {
    [clubsModule.mutations.setStickyClubs] (state, payload) {
        state.stickyClubs = payload
    },
    [clubsModule.mutations.setClub] (state, payload) {
        state.source.club = {...state.source.club, ...payload}
    },
    [clubsModule.mutations.setClub] (state, payload) {
        state.source.club = {...state.source.club, ...payload}
    },
    [clubsModule.mutations.setType] (state, type) {
        state.type = type
        localStorage.setItem('clubs-type', type)
    },
    [clubsModule.mutations.resetPagination](state) {
        state.page = 0
    },
    [clubsModule.mutations.setDateLoading](state, { date, value }) {
        let existedDateIndex = state.clubDates.findIndex(v => v.date === date)
        if (existedDateIndex === -1) {
            return []
        }
        state.clubDates[existedDateIndex].loading = value
    },
    [clubsModule.mutations.clubs.setPageNum](state, { value }) {
        if (value === '+1') {
            state.page++
        } else {
            state.page = value
        }
    },
    [clubsModule.mutations.clubs.set](state, clubs) {
        state.clubs = clubs
        console.log('state.clubs', state.clubs)
    },
    [clubsModule.mutations.clubs.update](state, { clubId, clubData }) {
        state.clubDates.forEach((dateEntry, dateIndex) => {
            dateEntry.clubs_range_low.forEach((club, index) => {
                if (club.id === clubId) {
                    Vue.set(
                        state.clubDates[dateIndex].clubs_range_low,
                        index,
                        {...club, ...clubData}
                    )
                }
            })
            dateEntry.clubs_range_high.forEach((club, index) => {
                if (club.id === clubId) {
                    Vue.set(
                        state.clubDates[dateIndex].clubs_range_high,
                        index,
                        {...club, ...clubData}
                    )
                }
            })
        })
    },
    [clubsModule.mutations.clubs.remove](state, clubId) {
        // state.clubDates.forEach((dateEntry, dateIndex) => {
        //     dateEntry.clubs_range_low.forEach((club, index) => {
        //         if (club.id === clubId) {
        //             Vue.delete(state.clubDates[dateIndex].clubs_range_low, index)
        //         }
        //     })
        //     dateEntry.clubs_range_high.forEach((club, index) => {
        //         if (club.id === clubId) {
        //             Vue.delete(state.clubDates[dateIndex].clubs_range_high, index)
        //         }
        //     })
        // })
    },
    [clubsModule.mutations.clubs.add](state, data) {
        if (!state.page) {
            state.clubs = data
        } else {
            Vue.set(state.clubs, 'clubs_nearby', state.clubs.clubs_nearby.concat(data.clubs_nearby))
            Vue.set(state.clubs, 'clubs_more', state.clubs.clubs_more.concat(data.clubs_more))
            Vue.set(state.clubs, 'clubs_remained', data.clubs_remained)
        }
        
    },
    [clubsModule.mutations.clubs.addDates](state, dates) {
        dates.forEach(newDateEntry => {
            let existedDateIndex = state.clubDates.findIndex(v => v.date === newDateEntry.date)
            if (existedDateIndex !== -1) {
                let existedEntry = _.cloneDeep(state.clubDates[existedDateIndex])
                if (newDateEntry.clubs_range_low.length) {
                    existedEntry.clubs_range_low.push(...newDateEntry.clubs_range_low)
                }

                if (newDateEntry.clubs_range_high.length) {
                    existedEntry.clubs_range_high.push(...newDateEntry.clubs_range_high)
                }

                existedEntry.clubs_range_high_count = newDateEntry.clubs_range_high_count

                Vue.set(state.clubDates, existedDateIndex, existedEntry)
            } else {
                newDateEntry.page = 0
                newDateEntry.loading = false
                state.clubDates.push(newDateEntry)
            }
        })
        console.log('')
    },
    [clubsModule.mutations.membership.add](state, clubId) {
        Vue.set(state.membershipRequests, clubId, true)
    },
    [clubsModule.mutations.membership.remove](state, clubId) {
        Vue.delete(state.membershipRequests, clubId)
    },
    [clubsModule.mutations.membership.addMy](state, clubId) {
        Vue.set(state.myMemberships, clubId, true)
    },
    [clubsModule.mutations.membership.removeMy](state, clubId) {
        Vue.delete(state.myMemberships, clubId)
    },
    [clubsModule.mutations.setLikes](state, club) {
        let index,
            clubDates = state.clubDates;

        for (let clubIndex in clubDates) {
            let clubsRangeHigh = clubDates[clubIndex].clubs_range_high,
                clubsRangeLow = clubDates[clubIndex].clubs_range_low

            if (clubsRangeHigh.length > 0) {
                index = _.findIndex(clubsRangeHigh, (e) => {
                    return e.id === club.id;
                })

                if (index !== -1) {
                    if (club.isLiked) {
                        club.likes++
                    } else {
                        club.likes--
                    }

                    Vue.set(state.clubDates[clubIndex].clubs_range_high, index, club);
                }
            }

            if (clubsRangeLow.length > 0) {
                index = _.findIndex(clubsRangeLow, (e) => {
                    return e.id === club.id;
                })

                if (index !== -1) {
                    if (club.isLiked) {
                        club.likes++
                    } else {
                        club.likes--
                    }

                    Vue.set(state.clubDates[clubIndex].clubs_range_low, index, club);
                }
            }

            if (this.state.clubsInfo[club.id]) {
                this.state.clubsInfo[club.id] = club;
            }
        }
    }
}

export default mutations
