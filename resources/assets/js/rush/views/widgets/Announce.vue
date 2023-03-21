<template>
    <div id="announce" v-if="announce.isShow">
        <div class="announceWrapper">
            <div class="announceClose" @click="hideAnnounce">
                <svg><use v-bind:xlink:href="getSvg('icon-close')"></use></svg>
            </div>
            <div class="announceWelcome" v-if="announce.type == 'welcome'">
                <div class="announceWelcomeTitle">
                    <img src="/images/rush/svg/rush_logo.svg" alt="Rush">
                    <span>{{ trans('rush.welcome.title') }}</span>
                </div>
                <ul>
                    <li>
                        <img src="/images/rush/welcome_tape.png" alt="Your own story" style="width: 80px">
                        <span>{{ trans('rush.welcome.story.title') }}</span>
                        <p>{{ trans('rush.welcome.story.description') }}</p>
                    </li>
                    <li>
                        <img src="/images/rush/welcome_calendar.png" alt="Post every day">
                        <span>{{ trans('rush.welcome.post.title') }}</span>
                        <p>{{ trans('rush.welcome.post.description') }}</p>
                    </li>
                    <li>
                        <img src="/images/rush/welcome_applause.png" alt="More applause?">
                        <span>{{ trans('rush.welcome.applause.title') }}</span>
                        <p>{{ trans('rush.welcome.applause.description') }}</p>
                    </li>
                    <li>
                        <img src="/images/rush/welcome_speaker.png" alt="Attention!" style="width: 80px">
                        <span>{{ trans('rush.welcome.attention.title') }}</span>
                        <p style="margin: 0 0 0 95px;">{{ trans('rush.welcome.attention.description') }}</p>
                    </li>
                </ul>
            </div>
            <div class="announceCreate" v-else-if="announce.type == 'create'">
                <div class="announceCreateTitle">
                    {{ trans('rush.how_to.title') }}
                </div>
                <ul>
                    <li>
                        <span>1.</span>
                        <div class="icon icon-plus icon-with-shadow">
                            <svg><use v-bind:xlink:href="getSvg('icon-plus')"></use></svg>
                        </div>
                        <p>{{ trans('rush.how_to.create') }}</p>
                        <div class="clear"></div>
                    </li>
                    <li>
                        <span>2.</span>
                        <div class="icon icon-title">
                            Title
                        </div>
                        <p>{{ trans('rush.how_to.name') }}</p>
                        <div class="clear"></div>
                    </li>
                    <li>
                        <span>3.</span>
                        <div class="icon icon-media">
                            <svg><use v-bind:xlink:href="getSvg('icon-photo_active')"></use></svg>
                        </div>
                        <p>{{ trans('rush.how_to.media') }}</p>
                        <div class="clear"></div>
                    </li>
                    <li>
                        <span>4.</span>
                        <div class="icon icon-send icon-with-shadow">
                            <svg><use v-bind:xlink:href="getSvg('icon-send_active')"></use></svg>
                        </div>
                        <p>{{ trans('rush.how_to.publish') }}</p>
                        <div class="clear"></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

import {
    _type as announceType
} from '@rush/modules/announce'

export default {
    mixins: [
        require('@rush/lib/mixin').default,
    ],
    computed: {
        ...mapGetters({
            announce: announceType.getters.announce,
        }),
    },
    methods: {
        ...mapActions({
            hideAnnounce: announceType.actions.hide,
        }),
    }
}
</script>
