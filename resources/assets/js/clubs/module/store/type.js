const clubsModule = {
    actions: {
        setType: 'clubs/actions/setType',
        initFilter: 'clubs/actions/initFilter',
        resetPagination: 'clubs/actions/resetPagination',
        clubs: {
            submit: 'clubs/actions/clubs/submit',
            remove: 'clubs/actions/clubs/remove',
            cleanUp: 'clubs/actions/clubs/cleanUp',
            update: 'clubs/actions/clubs/update',
            load: 'clubs/actions/clubs/load',
            loadMoreClubs: 'clubs/actions/clubs/loadMoreClubs',
            loadMoreByDate: 'clubs/actions/clubs/loadMoreByDate',
            loadInfo: 'clubs/actions/clubs/loadInfo',
            report: 'clubs/actions/clubs/report',
            like: 'clubs/actions/clubs/like',
        },
        membership: {
            set: 'clubs/actions/membership/set',
            setMy: 'clubs/actions/membership/setMy',
            add: 'clubs/actions/membership/add',
            addMy: 'clubs/actions/membership/addMy',
            remove: 'clubs/actions/membership/remove',
            removeMy: 'clubs/actions/membership/removeMy',

            request: 'clubs/actions/membership/request',
            requestAll: 'clubs/actions/membership/requestAll',
            update: 'clubs/actions/membership/update',

            subscribe: 'clubs/actions/membership/subscribe',
            unsubscribe: 'clubs/actions/membership/unsubscribe',
            unsubscribeFromAll: 'clubs/actions/membership/unsubscribeFromAll',
        },
        setClub: 'clubs/actions/setClub',
        setClub: 'clubs/actions/setClub',
        getStickyclubs: 'clubs/actions/getStickyclubs',
    },
    mutations: {
        setType: 'clubs/mutations/setType',
        resetPagination: 'clubs/mutations/resetPagination',
        setDateLoading: 'clubs/mutations/setDateLoading',
        clubs: {
            update: 'clubs/mutations/clubs/update',
            addDates: 'clubs/mutations/clubs/addDates',
            add: 'clubs/mutations/clubs/add',
            set: 'clubs/mutations/clubs/set',
            remove: 'clubs/mutations/clubs/remove',
            insert: 'clubs/mutations/clubs/insert',
            setPageNum: 'clubs/mutations/clubs/setPageNum',
        },
        membership: {
            add: 'clubs/mutations/membership/add',
            remove: 'clubs/mutations/membership/remove',
            addMy: 'clubs/mutations/membership/addMy',
            removeMy: 'clubs/mutations/membership/removeMy',
        },
        setClub: 'clubs/mutations/setClub',
        setClub: 'clubs/mutations/setClub',
        setLikes: 'clubs/mutations/setLikes',
        setStickyclubs: 'clubs/mutations/setStickyclubs',
    },
    getters: {
        type: 'clubs/getters/type',
        clubDates: 'clubs/getters/clubDates',
        clubIdsByDate: 'clubs/getters/clubIdsByDate',
        membershipRequests: 'clubs/getters/membershipRequests',
        hasMembershipRequests: 'clubs/getters/hasMembershipRequests',
        club: 'clubs/getters/club',
        clubs: 'clubs/getters/clubs',
        stickyclubs: 'clubs/getters/stickyclubs',
    }
}

export default clubsModule
