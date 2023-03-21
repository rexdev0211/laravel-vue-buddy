import discoverModule from "./type";

const getters = {
    [discoverModule.getters.usersAround]: (state) => {
        return state.usersAround || []
    },
    [discoverModule.getters.filterBuddies]: (state, getters) => {
        let filter = state.filter.filterType;
        let type;

        switch (filter) {
            case 'nearby':
                type = 'All';
                break;
            case 'recent':
                type = 'New';
                break;
            case 'favorites':
                type = 'Favorite';
                break;
            default:
                break;
        }

        return `${type} Buddies`
    },
    [discoverModule.getters.usersNextPageCount]: (state) => {
        return state.usersNextPageCount || 0
    },
    [discoverModule.getters.usersAroundInvalidated]: (state) => {
        return state.usersAroundVisible
            .filter((data) => {
                return moment().diff(data.loaded, 'seconds') >= window.REFRESH_LAST_ACTIVE_SECONDS
            })
            .map((data) => {
                return data.id
            })
    },
    // getDiscoverRequestData
    [discoverModule.getters.filter.requestParams]: (state, getters) => includeDistance => {
        let filter = getters[discoverModule.getters.filter.get]
        let filterValues = {
            filterType: filter('filterType'),
            page: state.filter.page
        }

        let filters = [
            'filterOnline', 'filterName', 'filterPics',
            'filterAge', 'filterHeight', 'filterWeight', 'filterPosition',
            'filterBody', 'filterPenis', 'filterDrugs', 'filterHiv',
            'filterVideos',
        ]

        filters.forEach(function(filterKey){
            let filterSet = state.filter[filterKey]
            let filterOptions = state.filter[`${filterKey}Values`]
            if (filterSet && !getters[discoverModule.getters.filter.isDisabled](filterKey)) {
                filterValues[filterKey] = filterOptions || filterSet
            }
        })

        if (!includeDistance) {
            return filterValues
        }

        return {...filterValues, distance: app.distance}
    },
    // getDiscoverOption
    [discoverModule.getters.filter.get]: (state) => (key) => {
        return state.filter[key] || null
    },
    // getDefaultFilterValues
    [discoverModule.getters.filter.default]: () => index =>  {
        let map = {
            viewType: 'grid',
            filterType: 'nearby',

            filterOnline: false,
            filterPics: false,
            filterVideos: false,
            filterAge: false,
            filterPosition: false,
            filterHeight: false,
            filterWeight: false,
            filterBody: false,
            filterPenis: false,
            filterDrugs: false,
            filterHiv: false,

            filterAgeValues: '',
            filterPositionValues: '',
            filterHeightValues: '',
            filterWeightValues: '',
            filterBodyValues: '',
            filterPenisValues: '',
            filterDrugsValues: '',
            filterHivValues: '',
        }
        return map[index] || null
    },

    [discoverModule.getters.filter.defaultFilter]: () => {
        return {
            viewType: 'grid',
            filterType: 'nearby',

            filterOnline: false,
            filterPics: false,
            filterVideos: false,
            filterAge: false,
            filterPosition: false,
            filterHeight: false,
            filterWeight: false,
            filterBody: false,
            filterPenis: false,
            filterDrugs: false,
            filterHiv: false,

            filterAgeValues: false,
            filterPositionValues: false,
            filterHeightValues: false,
            filterWeightValues: false,
            filterBodyValues: false,
            filterPenisValues: false,
            filterDrugsValues: false,
            filterHivValues: false,
        }
    },
    // getTotalFiltersCount
    [discoverModule.getters.filter.countEnabled]: (state, getters) => {
        let total = 0
        let filters = [
            'filterAge', 'filterPosition', 'filterHeight', 'filterWeight', 'filterPenis',
            'filterDrugs', 'filterHiv', 'filterBody',
        ]

        filters.forEach(function(filterKey){
            let filterDisabled = getters[discoverModule.getters.filter.isDisabled](filterKey)
            total += (state.filter[filterKey] === true && !filterDisabled ? 1 : 0)
        })

        return total
    },
    // isFilterDisabled
    [discoverModule.getters.filter.isDisabled]: () => filterKey =>  {
        const proFilters = [
            'filterVideos', 'filterHeight', 'filterWeight',
            'filterBody', 'filterPenis', 'filterDrugs', 'filterHiv'
        ]
        
        console.log('Check if disabled', filterKey, app, app.userIsPro);

        return !app.userIsPro && proFilters.includes(filterKey)
    },
    [discoverModule.getters.filter.proFilters]: () => {
        return [
            'filterVideos', 'filterHeight', 'filterWeight',
            'filterBody', 'filterPenis', 'filterDrugs', 'filterHiv'
        ]
    }
}

export default getters