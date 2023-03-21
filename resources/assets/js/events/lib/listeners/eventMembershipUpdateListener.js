import eventsModule from "@events/module/store/type";
import chatModule from "@chat/module/store/type";
import auth from '@general/lib/auth';

export default e => {
    let payload = e.payload;

    let currentUserId = auth.getUserId();
    let eventData = payload.event;
    let eventId = payload.event_id;
    let action = payload.action;

    console.log('[Membership event listener] Signal received', e);

    switch (action) {
        // To host only
        case 'request':{
            // Add membership request flag to event
            store.dispatch(eventsModule.actions.membership.add, eventId)

            // Add user's has_event_notifications flag
            if (
                app.$route.name !== 'events'
                ||
                app.isMobile
            ) {
                store.commit('updateUser', { has_event_notifications: true })
            }

            // Add user's has_club_notifications flag
            if (
                app.$route.name !== 'clubs'
                ||
                app.isMobile
            ) {
                store.commit('updateUser', { has_club_notifications: true })
            }

            // Update event
            store.dispatch(eventsModule.actions.events.update, eventData)
            break
        }
        case 'leave':
        case 'accept':{
            // Except host and recipient
            if (!(payload.ignore_recipient_id || []).includes(currentUserId)) {
                // Update event
                store.dispatch(eventsModule.actions.events.update, eventData)
            }

            // Accepted user
            if (payload.recipient_id == currentUserId) {
                // Update event
                store.dispatch(eventsModule.actions.events.update, eventData)

                // Show notification in case of membership was accepted
                if (action === 'accept') {
                    if (eventData.type !== 'club') {
                        // Add user's has_event_notifications flag
                        store.commit('updateUser', { has_event_notifications: true })
                        
                        // Add membership to myMemberships
                        store.dispatch(eventsModule.actions.membership.addMy, eventId)
                        // Add event to myEvents list
                        store.commit('addMyEvents', [eventData.general])
                    } else {
                        // Add user's has_club_notifications flag
                        store.commit('updateUser', { has_club_notifications: true })
                        
                    }
                    
                }
            }
            break
        }
        case 'reject':
        case 'remove':
        case 'remove_event':{
            // Except host and recipient
            if (
                action !== 'remove_event'
                &&
                !(payload.ignore_recipient_id || []).includes(currentUserId)
            ) {
                store.dispatch(eventsModule.actions.events.update, eventData)
            }

            if (
                // Removed user
                (payload.recipient_id == currentUserId && action === 'remove')
                ||
                // Everybody except host in removed event
                (
                    !(payload.ignore_recipient_id || []).includes(currentUserId)
                    &&
                    action === 'remove_event'
                )
            ) {
                store.dispatch(eventsModule.actions.events.cleanUp, {
                    eventId,
                    removeFromEventList: action === 'remove_event'
                })
            }
            break
        }
    }
}