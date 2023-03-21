import _ from "lodash";
import notificationsModule from "./type";
import { convertUtcToLocal } from "@chat/lib/helpers";

const mutations = {
    [notificationsModule.mutations.update](state, { userId, fields }) {
        state.notifications.forEach((entry, index) => {
            if (entry.user_from.id == userId) {
                state.notifications[index].user_from = {...entry.user_from, ...fields}
            }
        })

        state.visitors.forEach((entry, index) => {
            if (entry.visitor.id == userId) {
                state.visitors[index].visitor = {...entry.visitor, ...fields}
            }
        })

        state.visited.forEach((entry, index) => {
            if (entry.id == userId) {
                state.visited[index] = {...entry, ...fields}
            }
        })
    },
    [notificationsModule.mutations.remove](state, { userId }) {
        state.notifications.forEach((entry, index) => {
            if (entry.user_from.id == userId) {
                Vue.delete(state.notifications, index)
            }
        })

        state.visitors.forEach((entry, index) => {
            if (entry.visitor.id == userId) {
                Vue.delete(state.visitors, index)
            }
        })

        state.visited.forEach((entry, index) => {
            if (entry.visited.id == userId) {
                Vue.delete(state.visited, index)
            }
        })
    },
    [notificationsModule.mutations.setVisibility](state, { group, visible }) {
        state.visibility[group] = visible
    },

	// sortNotifications
	[notificationsModule.mutations.notifications.sort](state) {
        console.log('[Mutation] notifications.sort')
        state.notifications.sort((a, b) => {
			if (a.idate < b.idate) {
				return 1;
			}
			if (a.idate > b.idate) {
				return -1;
			}
			return 0;
		});
	},

	// setNotifications
    [notificationsModule.mutations.notifications.set](state, notifications) {
        notifications.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });
        state.notifications = notifications;
    },
	// addNotification
    [notificationsModule.mutations.notifications.addSingle](state, notification) {
        notification.idate = convertUtcToLocal(notification.idate)
        state.notifications.push(notification)
    },
	// prependNotifications
    [notificationsModule.mutations.notifications.unshift](state, payload) {
        const notifications = payload.constructor == Array ? payload : [payload]
        notifications.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });
        state.notifications.unshift(...notifications);
    },
	// appendNotifications
    [notificationsModule.mutations.notifications.push](state, payload) {
        const notifications = payload.constructor == Array ? payload : [payload]
        notifications.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });
        state.notifications.push(...notifications);
    },

	// setVisitors
    [notificationsModule.mutations.visitors.set](state, visitors) {
        visitors.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });
        state.visitors = visitors;
    },
	// prependVisitors
    [notificationsModule.mutations.visitors.unshift](state, payload) {
        const visitors = payload.constructor == Array ? payload : [payload]
        visitors.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });

        // Delete previous visitor's records
        visitors.forEach(function(el){
            state.visitors.forEach(function(stateEl, stateIndex){
                if (el.visitor_id == stateEl.visitor_id) {
                    Vue.delete(state.visitors, stateIndex)
                }
            })
        })

        state.visitors.unshift(...visitors);
    },
	// appendVisitors
    [notificationsModule.mutations.visitors.push](state, payload) {
        const visitors = payload.constructor == Array ? payload : [payload]
        visitors.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });
        state.visitors.push(...visitors);
    },

	// setVisited
    [notificationsModule.mutations.visited.set](state, visited) {
        visited.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });
        state.visited = visited;
    },
	// prependVisited
    [notificationsModule.mutations.visited.unshift](state, payload) {
        const visited = payload.constructor == Array ? payload : [payload]
        visited.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });
        state.visited.unshift(...visited);
    },
	// appendVisited
    [notificationsModule.mutations.visited.push](state, payload) {
        const visited = payload.constructor == Array ? payload : [payload]
        visited.forEach(el => {
            el.idate = convertUtcToLocal(el.idate);
        });
        state.visited.push(...visited);
    },
}

export default mutations
