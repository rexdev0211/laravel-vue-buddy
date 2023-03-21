import mutations from "./store/mutations";
import actions from "./store/actions";
import getters from "./store/getters";

/* Module */
const _module = {
    state: () => ({
        refreshQueued: true,
        type: 'discover',
        membershipRequests: {},
        myMemberships: {},
        stickyClubs: {
            'fun': [],
            'guide': [],
            'bang': [],
        },
        clubDates: [
            /*[
                date: "2021-01-01",         // From the response
                clubs_range_low: [],       // From the response
                clubs_range_high: [],      // From the response
                clubs_range_low_count: 0,  // From the response
                clubs_range_high_count: 0, // From the response
                page: 0,                    // Set dynamically
                loading: false              // Set dynamically
            ]*/
        ],
        clubs: {
            clubs_nearby: [],
            clubs_more: []
        },
        page: 0,
        source: {
            club: {
                visible: false,
                mode: 'view',
                clubId: null
            },
        }
    }),
    actions,
    mutations,
    getters
}

export default _module
