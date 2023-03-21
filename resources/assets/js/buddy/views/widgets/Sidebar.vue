<template>
    <div class="mobileSidebarHolder positionRight" :class="{'active': isSidebarActive}">
        <div class="mobileSidebarHide" @click="hideSidebar"></div>
        <div class="mobileSidebar">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import {mapGetters, mapActions} from 'vuex'

import {
    _type as sidebarType
} from '@general/modules/sidebar'

export default {
    computed: {
        ...mapGetters({
            isSidebarActive: sidebarType.getters.active,
        }),
    },
    methods: {
        ...mapActions({
            hideSidebar: sidebarType.actions.hide,
        }),
    },
}
</script>
