import {
    _type as userType,
} from '../modules/user.js'

import {
    _type as rushesType,
} from '../modules/rushes.js'

import { mapActions } from 'vuex'

export default {
    computed: {
        isDesktop() {
            return this.$resize && this.$mq.above(800) && !this.isApp
        },
        isMobile() {
            return !this.isDesktop
        },
        isApp() {
            return location.hostname == window.app.appDomain
        },
    },
    methods: {
        ...mapActions({
            setRushes:             rushesType.actions.set,
            setRushFavorites:      userType.actions.rush.favorites.set,
            setRushQueue:          userType.actions.rush.queue.set,
            setFavoritesQueue:     userType.actions.rush.queue.favorites,
        }),
        refreshFavorites(favorites) {
            this.setRushFavorites(favorites.list)
            this.setFavoritesQueue(favorites.queue)
        },
        refreshData() {
            let v = this

            axios.get('/api/rush/refresh').then(({data}) => {
                v.setRushes(data.rushes)
                v.setRushQueue(data.queue)
                v.setRushFavorites(data.favorites)
            })
            .catch((error) => {
                console.log(error)
            });
        },
        getFormData(obj) {
            const fd = new FormData();

            Object.keys(obj).forEach(key => {
                if (obj[key].constructor === Array) {
                    obj[key].forEach(value => {
                        fd.append(`${key}[]`, value)
                    })
                } else {
                    fd.append(key, obj[key]);
                }
            });

            return fd;
        },
        trans(word, args) {
            return this.$t(word, args);
        },
        getSvg(icon) {
            return symbolsSvgUrl(icon);
        },
    },
}
