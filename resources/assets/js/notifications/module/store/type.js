const notificationsModule = {
    actions: {
		setVisibleExclusive: 'notifications/actions/setVisibleExclusive',
    	setVisibility: 'notifications/actions/setVisibility',

		update: 'notifications/actions/update',
		remove: 'notifications/actions/remove',
		addVisit: 'notifications/actions/addVisit',

		visitors: {
			set: 'notifications/actions/visitors/set',
			unshift: 'notifications/actions/visitors/unshift',
			push: 'notifications/actions/visitors/push',
			load: 'notifications/actions/visitors/load',
		},
		visited: {
			set: 'notifications/actions/visited/set',
			unshift: 'notifications/actions/visited/unshift',
			push: 'notifications/actions/visited/push',
			load: 'notifications/actions/visited/load',
		},
		notifications: {
			set: 'notifications/actions/notifications/set',
			unshift: 'notifications/actions/notifications/unshift',
			push: 'notifications/actions/notifications/push',
			load: 'notifications/actions/notifications/load',
			addWave: 'notifications/actions/notifications/addWave',
		},
    },
    mutations: {
		update: 'notifications/mutations/update',
		remove: 'notifications/mutations/remove',
		setVisibility: 'notifications/mutations/setVisibility',

		visitors: {
			set: 'notifications/mutations/visitors/set',
			unshift: 'notifications/mutations/visitors/unshift',
			push: 'notifications/mutations/visitors/push'
		},
		visited: {
			set: 'notifications/mutations/visited/set',
			unshift: 'notifications/mutations/visited/unshift',
			push: 'notifications/mutations/visited/push'
		},
		notifications: {
			sort: 'notifications/mutations/notifications/sort',
			set: 'notifications/mutations/notifications/set',
			unshift: 'notifications/mutations/notifications/unshift',
			push: 'notifications/mutations/notifications/push'
		},
		myTaps: {
			addSingle: 'notifications/mutations/myTaps/addSingle',
			set: 'notifications/mutations/myTaps/set'
		}
    },
    getters: {
    	visibility: {
			getType: 'notifications/getters/visibility/getType',
			any: 'notifications/getters/visibility/any',
		},
        notifications: {
			wave: 'notifications/getters/notifications/wave'
		}
    }
}

export default notificationsModule