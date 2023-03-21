import notificationsModule from "./type";

const getters = {
    [notificationsModule.getters.notifications.wave]: state => {
        return state.notifications.filter(e => e.type == 'wave') || []
    },
    [notificationsModule.getters.visibility.getType]: state => type => {
        return state.visibility[type] || null
    },
    [notificationsModule.getters.visibility.any]: (state) =>  {
        return state.visibility.notifications || state.visibility.visitors || state.visibility.visited
    },
}

export default getters