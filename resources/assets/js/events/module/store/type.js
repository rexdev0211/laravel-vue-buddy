const eventsModule = {
    actions: {
        setType: 'events/actions/setType',
        initFilter: 'events/actions/initFilter',
        resetPagination: 'events/actions/resetPagination',
        events: {
            submit: 'events/actions/events/submit',
            remove: 'events/actions/events/remove',
            cleanUp: 'events/actions/events/cleanUp',
            update: 'events/actions/events/update',
            load: 'events/actions/events/load',
            loadMoreByDate: 'events/actions/events/loadMoreByDate',
            loadInfo: 'events/actions/events/loadInfo',
            report: 'events/actions/events/report',
            like: 'events/actions/events/like',
        },
        membership: {
            set: 'events/actions/membership/set',
            setMy: 'events/actions/membership/setMy',
            add: 'events/actions/membership/add',
            addMy: 'events/actions/membership/addMy',
            remove: 'events/actions/membership/remove',
            removeMy: 'events/actions/membership/removeMy',

            request: 'events/actions/membership/request',
            update: 'events/actions/membership/update',

            subscribe: 'events/actions/membership/subscribe',
            unsubscribe: 'events/actions/membership/unsubscribe',
            unsubscribeFromAll: 'events/actions/membership/unsubscribeFromAll',
        },
        setBang: 'events/actions/setBang',
        setEvent: 'events/actions/setEvent',
        getStickyEvents: 'events/actions/getStickyEvents',
    },
    mutations: {
        setType: 'events/mutations/setType',
        resetPagination: 'events/mutations/resetPagination',
        setDateLoading: 'events/mutations/setDateLoading',
        events: {
            update: 'events/mutations/events/update',
            addDates: 'events/mutations/events/addDates',
            set: 'events/mutations/events/set',
            remove: 'events/mutations/events/remove',
            insert: 'events/mutations/events/insert',
            setPageNum: 'events/mutations/events/setPageNum',
        },
        membership: {
            add: 'events/mutations/membership/add',
            remove: 'events/mutations/membership/remove',
            addMy: 'events/mutations/membership/addMy',
            removeMy: 'events/mutations/membership/removeMy',
        },
        setBang: 'events/mutations/setBang',
        setEvent: 'events/mutations/setEvent',
        setLikes: 'events/mutations/setLikes',
        setStickyEvents: 'events/mutations/setStickyEvents',
    },
    getters: {
        type: 'events/getters/type',
        eventDates: 'events/getters/eventDates',
        eventIdsByDate: 'events/getters/eventIdsByDate',
        membershipRequests: 'events/getters/membershipRequests',
        hasMembershipRequests: 'events/getters/hasMembershipRequests',
        bang: 'events/getters/bang',
        event: 'events/getters/event',
        stickyEvents: 'events/getters/stickyEvents',
    }
}

export default eventsModule
