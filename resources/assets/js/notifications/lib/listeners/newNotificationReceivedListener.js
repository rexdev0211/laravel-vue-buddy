import notificationsModule from "@notifications/module/store/type";

export default e => {
    let notification = e.notificationData
    console.log('[Notification listener] Signal received', e, notification)

    store.dispatch(notificationsModule.actions.notifications.unshift, notification)

    let notificationsVisible = store.getters[notificationsModule.getters.visibility.getType]('notifications')
    let dropdownVisible = store.getters[notificationsModule.getters.visibility.any]

    console.log('[Notification listener] New notification conditions', { notificationsVisible, dropdownVisible })

    // Dropdown is not visible
    if (!dropdownVisible) {
        let newValue = {
            has_new_notifications: true,
            has_notifications: true,
        }
        store.commit('updateUser', newValue)

    // Dropdown is visible, but notifications are not
    } else if (dropdownVisible && !notificationsVisible) {
        store.commit('updateUser', { has_new_notifications: true })
        axios.post('/api/updateUser', { has_notifications: false })

    // Dropdown is visible, notifications are visible
    } else if (dropdownVisible && notificationsVisible) {
        let newValue = {
            has_new_notifications: false,
            has_notifications: false
        }
        store.commit('updateUser', newValue)
        axios.post('/api/updateUser', newValue)
    }
}
