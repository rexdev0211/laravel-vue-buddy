import clubsModule from "./type";

const getters = {
    [clubsModule.getters.membershipRequests]: state => clubId => {
        return state.membershipRequests[clubId] || false
    },
    [clubsModule.getters.club]: state => {
        return state.source.club
    },
    [clubsModule.getters.bang]: state => {
        return state.source.bang
    },
    [clubsModule.getters.type]: state => {
        return state.type
    },
    [clubsModule.getters.clubs]: state => {
        return state.clubs
    },
    [clubsModule.getters.clubDates]: state => {
        return state.clubDates
    },
    [clubsModule.getters.clubIdsByDate]: state => date => {
        let existedDateIndex = state.clubDates.findIndex(v => v.date === date)
        if (existedDateIndex === -1) {
            return []
        }
        let existedEntry = state.clubDates[existedDateIndex]
        let lowRangeClubIds = existedEntry.clubs_range_low.map(club => {
            return club.id
        })
        return lowRangeClubIds
    },
    [clubsModule.getters.stickyClubs]: state => {
        return state.stickyClubs[state.type]
    },
}

export default getters
