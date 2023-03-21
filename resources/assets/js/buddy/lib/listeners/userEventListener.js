// import mixin from '@general/lib/mixin;

export default e => {
    let msg = e.messageData;

    if (msg.event == 'delete' || msg.event == 'suspend') {
        app.logout();

        window.index.commit('logout');
    }
}