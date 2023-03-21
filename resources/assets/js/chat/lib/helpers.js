export const sortConversationsFunc = (a, b) => {
    if (a.message.idate < b.message.idate) {
        return 1;
    }
    if (a.message.idate > b.message.idate) {
        return -1;
    }
    return 0;
}

export const convertUtcToLocal = (idate) => {
    return moment.utc(idate).local().format('YYYY-MM-DD HH:mm:ss');
}

export const conversationsFullyScrolled = (chatType) => {
    let container;

    if (app.isMobile) {
        container = document.getElementById('application-wrapper');
    } else {
        container = document.getElementById(`conversation-${chatType}-scroll`);
    }

    if (!container) {
        return false;
    }

    let currentScroll = container.scrollTop;
    let scrollHeight = container.scrollHeight;
    let clientHeight = container.clientHeight;

    return currentScroll + clientHeight >= scrollHeight;
}

export const scrollToNewMessages = (iteration = 1) => {
    let msgs = $(".messages-box");
    if (!msgs.length) {
        return
    }

    msgs.scrollTop(msgs[0].scrollHeight);
    if (iteration++ > 5) {
        return
    }

    //some images may load in the inner time, immediately it will say 0 position, but after a few ms it will be positive
    //actually I already fixed it with images fixed height
    setTimeout(() => {
        scrollToNewMessages(iteration);
    }, 100);
}

export const scrollToLastMessage = (chatType) => {
    const ids = ['js-chat-user-cmp', 'js-chat-group-cmp']
    const elem = document.getElementsByClassName('wrap')[0];
    let childNodeId;

    if (!elem) {
        return;
    }

    for (let i = 0; i < elem.childNodes.length; i++) {
        if (ids.includes(elem.childNodes[i].id)) {
            childNodeId = elem.childNodes[i].id;
        }
    }

    if (app.isMobile) {
        let lastMessageElem = document.querySelector(`#${childNodeId}`).lastElementChild
        lastMessageElem.scrollIntoView({block: "center"})
    } else {
        const scrollDiv = document.getElementById(`conversation-${chatType}-scroll`);
        if (!scrollDiv) {
            return;
        }
        const lastMessageElem = document.querySelector(`#${childNodeId}`).lastElementChild
        const coordinates = scrollDiv.scrollHeight - lastMessageElem.offsetHeight;

        scrollDiv.scroll({
            top: coordinates,
        })
    }

}

export const beep = () => {
    if (store.state.profile.notification_sound !== 'yes') {
        return
    }
    if (!store.state.isTapped) {
        return
    }
    let snd = new Audio("/sounds/notification.mp3")
    snd.play()
}