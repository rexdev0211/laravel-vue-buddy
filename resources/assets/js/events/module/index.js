import mutations from "./store/mutations";
import actions from "./store/actions";
import getters from "./store/getters";

/* Module */
const _module = {
    state: () => ({
        refreshQueued: true,
        type: 'guide',
        membershipRequests: {},
        myMemberships: {},
        stickyEvents: {
            'fun': [],
            'guide': [],
            'bang': [],
        },
        eventDates: [
            /*[
                date: "2021-01-01",         // From the response
                events_range_low: [],       // From the response
                events_range_high: [],      // From the response
                events_range_low_count: 0,  // From the response
                events_range_high_count: 0, // From the response
                page: 0,                    // Set dynamically
                loading: false              // Set dynamically
            ]*/
        ],
        page: 0,
        source: {
            bang: {
                visible: false,
                mode: 'view',
                eventId: null
            },
            event: {
                visible: false,
                mode: 'view',
                eventId: null
            },
        }
    }),
    actions,
    mutations,
    getters
}

export default _module
