import notificationsModule from "@notifications/module/store/type";

export default e => {
    let visitors = e.visitorData.visitors;
    console.log('[Visitor listener] Signal received', e, visitors)

    store.dispatch(notificationsModule.actions.visitors.unshift, visitors)

    let visitorsVisible = store.getters[notificationsModule.getters.visibility.getType]('visitors')
    let notificationsVisible = store.getters[notificationsModule.getters.visibility.any]

    //console.log('New visitor conditions', { visitorsVisible, notificationsVisible })

    // Notifications are not visible
    if (!notificationsVisible) {
        let newValue = {
            has_new_visitors: true,
            has_notifications: true
        }
        store.commit('updateUser', newValue)
        //console.log('Notifications are not visible')

    // Notifications are visible, but visitors are not
    } else if (notificationsVisible && !visitorsVisible) {
        store.commit('updateUser', { has_new_visitors: true })
        axios.post('/api/updateUser', { has_notifications: false })
        //console.log('Notifications are visible, but visitors are not')

    // Notifications are visible, visitors too
    } else if (notificationsVisible && visitorsVisible) {
        let newValue = {
            has_new_visitors: false,
            has_notifications: false
        }
        store.commit('updateUser', newValue)
        axios.post('/api/updateUser', newValue)
        //console.log('Notifications are visible, visitors too')
    }
}
