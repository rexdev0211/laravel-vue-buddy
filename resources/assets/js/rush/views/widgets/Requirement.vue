<template>
    <div class="requirement" v-if="requirementState.isShow">
        <div class="requirementContainer">
            <div class="requirementClose" @click="hideRequirement">
                <svg><use v-bind:xlink:href="getSvg('icon-close')"></use></svg>
            </div>
            <img :src="requirementState.image" :alt="requirementState.title" :style="requirementState.imageStyle" />
            <h3>{{ requirementState.title }}</h3>
            <p>
                {{ requirementState.description }}
                <span class="requirementProIcon" v-if="requirementState.withProIcon">
                    <svg><use v-bind:xlink:href="getSvg('pro')"></use></svg>
                </span>
            </p>
            <div class="requirementButton">
                <a @click="redirectTo">{{ requirementState.button }}</a>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

import {
    _type as requirementsType
} from '@rush/modules/requirements'

export default {
    mixins: [
        require('@rush/lib/mixin').default,
    ],
    computed: {
        ...mapGetters({
            requirementState: requirementsType.getters.state,
        }),
    },
    methods: {
        ...mapActions({
            hideRequirement: requirementsType.actions.hide,
        }),
        redirectTo() {
            if (this.requirementState.type == 'change_settings') {
                window.open(app.url + '/profile/settings', '_system')
            } else {
                window.location = '/profile/pro'
            }
            this.hideRequirement()
        }
    }
}
</script>
