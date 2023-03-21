export const convertUtcToLocal = (idate) => {
    return moment.utc(idate).local().format('YYYY-MM-DD HH:mm:ss');
}

export const storedFormFilters = {
    filterName:     { ranged: false },
    filterType:     { ranged: false },
    viewType:       { ranged: false },
    filterOnline:   { ranged: false },
    filterPics:     { ranged: false },
    filterVideos:   { ranged: false },
    filterAge:      { ranged: true },
    filterPosition: { ranged: true },
    filterHeight:   { ranged: true },
    filterWeight:   { ranged: true },
    filterBody:     { ranged: true },
    filterPenis:    { ranged: true },
    filterDrugs:    { ranged: true },
    filterHiv:      { ranged: true },
}

export const loadLocalStorageValue = (key) => {
    let value = localStorage.getItem('discover-' + key)

    try {
        let jsonValue = JSON.parse(value)
        if (jsonValue !== null) {
            value = jsonValue
        }
    } catch (error) {
    }

    if (['true', 'false'].includes(value)) {
        value = (value === 'true')
    }

    if (value === 'null') {
        value = null
    }

    return value
}

export const loadPersonalLocalStorageValue = (key) => {
    let userId = localStorage.getItem('userId'),
        value  = localStorage.getItem('discover-' + userId + '-' + key)

    try {
        let jsonValue = JSON.parse(value)
        if (jsonValue !== null) {
            value = jsonValue
        }
    } catch (error) {

    }

    if (['true', 'false'].includes(value)) {
        value = (value === 'true')
    }

    if (value === 'null') {
        value = null
    }

    return value
}