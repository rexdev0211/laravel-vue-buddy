import mutations from "./store/mutations";
import actions from "./store/actions";
import getters from "./store/getters";

/* Module */
const _module = {
    state: () => ({
        refreshQueued: true,
        usersAround: [],
        usersNextPageCount: 0,
        usersAroundVisible: [],
        filterBuddies: 'All',
        filter: {
            page: 0
        },
    }),
    actions,
    mutations,
    getters
}

export default _module