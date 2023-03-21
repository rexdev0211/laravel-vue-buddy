import auth from './auth'
import Echo from 'laravel-echo'

import newVisitorReceivedListener      from './listeners/newVisitorReceivedListener'
import newMessageReceivedListener      from './listeners/newMessageReceivedListener'
import newEventMessageReceivedListener from './listeners/newEventMessageReceivedListener'
import newNotificationReceivedListener from './listeners/newNotificationReceivedListener'

(function createEchoInstance() {
    let token = 'not_defined_yet'

    if(typeof io !== 'undefined' && typeof window.Echo == 'undefined') {
        window.Echo = new Echo({
            broadcaster: 'socket.io',
            host: window.location.hostname + ':' + window.app.socketJsPort,
            auth: {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            }
        })
    }
})()

export const listener = () => {
    if(!auth.isAuthenticated()) {
        console.log("[Echo] Can't listen because I'm unauthenticated")

        return;
    }

    if(typeof window.Echo == 'undefined') {
        console.log("[Echo] Can't listen because Echo is not defined")

        return;
    }

    let userId = auth.getUserId()
    let token  = auth.getToken()

    window.Echo.connector.options.auth.headers.Authorization = 'Bearer ' + token

    console.log('[Echo] Listening to channel-' + userId)

    window.Echo.private('channel-'+userId)
               .listen('NewVisitorReceived', newVisitorReceivedListener)
               .listen('NewMessageReceived', newMessageReceivedListener)
               .listen('NewEventMessageReceived', newEventMessageReceivedListener)
               .listen('NewNotificationReceived', newNotificationReceivedListener)
};

export const stopListening = () => {
    if(typeof window.Echo == 'undefined') {
        console.log("[Echo] Can't stop listening because Echo is not defined")
        return;
    }

    let userId = auth.getUserId()

    window.Echo.leave('channel-'+userId)

    console.log('[Echo] Leaved channel-' + userId)
};
