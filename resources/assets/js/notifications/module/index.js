import mutations from "./store/mutations";
import actions from "./store/actions";
import getters from "./store/getters";

/* Module */
const _module = {
    state: () => ({
        visibility: {
            notifications: false,
            visited: false,
            visitors: false
        },
		notifications: [],
		visitors: [],
		visited: [],
    }),
    actions,
    mutations,
    getters
}

export default _module