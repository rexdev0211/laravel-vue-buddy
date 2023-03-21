import notificationsModule from "./type";
import auth from '@general/lib/auth';
import _ from 'lodash'

const actions = {
    [notificationsModule.actions.update](store, payload) {
        store.commit(notificationsModule.mutations.update, payload)
    },
    [notificationsModule.actions.remove](store, payload) {
        store.commit(notificationsModule.mutations.remove, payload)
    },
    [notificationsModule.actions.setVisibleExclusive](store, payload) {
        store.commit(notificationsModule.mutations.setVisibility, payload)

        let hideGroups = _.without(['notifications', 'visited', 'visitors'], payload.group)
        hideGroups.forEach(function(group){
            store.commit(notificationsModule.mutations.setVisibility, { group, visible: false})
        })
    },
    [notificationsModule.actions.setVisibility](store, payload) {
        store.commit(notificationsModule.mutations.setVisibility, payload)
    },

	// notifications
    [notificationsModule.actions.notifications.set](store, payload) {
        store.commit(notificationsModule.mutations.notifications.set, payload)
        store.commit(notificationsModule.mutations.notifications.sort)
    },
    [notificationsModule.actions.notifications.addSingle](store, payload) {
        store.commit(notificationsModule.mutations.notifications.addSingle, payload)
        store.commit(notificationsModule.mutations.notifications.sort)
    },
    [notificationsModule.actions.notifications.unshift](store, payload) {
        store.commit(notificationsModule.mutations.notifications.unshift, payload)
    },
    [notificationsModule.actions.notifications.push](store, payload) {
        store.commit(notificationsModule.mutations.notifications.push, payload)
    },
    async [notificationsModule.actions.notifications.load](store) {
        const lastNotification = store.state.notifications.slice(-1)[0]
        const lastNotificationId = lastNotification ? lastNotification.id : 0

        let response = await axios.post('/api/getNotifications', { lastNotificationId })
        if (response.status === 200) {
            store.commit(notificationsModule.mutations.notifications.push, response.data)
            return response.data.length
        } else {
            return 0
        }
    },
	[notificationsModule.actions.notifications.addWave](store, { recipientId, type, callback}) {
		axios
			.post(`/api/addWave`, { recipientId, type })
			.then((res) => { 
				callback(res) 
			})
	},
	
	// visitors
    [notificationsModule.actions.visitors.set](store, payload) {
        store.commit(notificationsModule.mutations.visitors.set, payload)
    },
    [notificationsModule.actions.visitors.unshift](store, payload) {
        store.commit(notificationsModule.mutations.visitors.unshift, payload)
    },
    [notificationsModule.actions.visitors.push](store, payload) {
        store.commit(notificationsModule.mutations.visitors.push, payload)
    },
    async [notificationsModule.actions.visitors.load](store) {
        const lastVisitor = store.state.visitors.slice(-1)[0]
        const lastVisitId = lastVisitor ? lastVisitor.id : 0

        let response = await axios.post('/api/getVisitors', { lastVisitId })
        if (response.status === 200) {
            store.commit(notificationsModule.mutations.visitors.push, response.data)
            return response.data.length
        } else {
            return 0
        }
    },

	// visited
    [notificationsModule.actions.visited.set](store, payload) {
        store.commit(notificationsModule.mutations.visited.set, payload)
    },
    [notificationsModule.actions.visited.add](store, payload) {
        store.commit(notificationsModule.mutations.visited.add, payload)
    },
    [notificationsModule.actions.visited.push](store, payload) {
        store.commit(notificationsModule.mutations.visited.push, payload)
    },
    async [notificationsModule.actions.visited.load](store) {
        const lastVisited = store.state.visited.slice(-1)[0]
        const lastVisitId = lastVisited ? lastVisited.id : 0

        let response = await axios.post('/api/getVisitedUsers', { lastVisitId })
        if (response.status === 200){
            store.commit(notificationsModule.mutations.visited.push, response.data)
            return response.data.length
        } else {
            return 0
        }
    },
    async [notificationsModule.actions.addVisit](store, { userId }) {
        if (userId != auth.getUserId()) {
            let response = await axios.post(`/api/addVisit`, { userId })
            if (response.data) {
                store.dispatch(notificationsModule.actions.visited.set, response.data.visited);
            }
        }
    },
}

export default actions