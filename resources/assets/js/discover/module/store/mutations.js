import discoverModule from "./type";
import moment from 'moment'

const mutations = {
    // setUsersAround
    [discoverModule.mutations.users.set](state, payload) {
        state.usersAround = payload
    },
    [discoverModule.mutations.users.setVisible](state, payload) {
        state.usersAroundVisible = payload
    },
    // addUsersAround
    [discoverModule.mutations.users.push](state, payload) {
        payload.forEach(user => {
            user.loaded = moment()
        })
        state.usersAround.push(...payload);
    },
    [discoverModule.mutations.usersNextPageCount.push](state, payload) {
        state.usersNextPageCount = payload;
    },
    [discoverModule.mutations.users.remove](state, { userId }) {
        const index = state.usersAround.findIndex(e => e.id == userId)
        if (index !== -1) {
            state.usersAround.splice(index, 1)
        }
    },
    [discoverModule.mutations.users.update](state, { userId, fields }) {
        let index = state.usersAround.findIndex(v => v.id == userId);
        if (index !== -1) {
            Vue.set(state.usersAround, index, {...state.usersAround[index], ...fields})
        }
    },

    [discoverModule.mutations.users.setRefreshQueued](state, val) {
        state.refreshQueued = val
    },
    [discoverModule.mutations.filter.setPage](state, payload) {
        state.filter.page = payload
    },
    [discoverModule.mutations.filter.set](state, { key, value }) {
        Vue.set(state.filter, key, value)
    },
    [discoverModule.mutations.filterBuddies.set](state, { type }) {
        state.filterBuddies = type;
    },
    [discoverModule.mutations.filter.remove](state, {key}) {
        Vue.set(state.filter, key, null);
    }
}

export default mutations