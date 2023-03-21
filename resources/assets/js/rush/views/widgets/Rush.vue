<template>
      <div class="rushes">
          <div v-for="rushesGroup in rushes" class="rushesGroup" :class="{'withBest': rushesGroup.best ? true : false}">
              <div class="rushItem rushBest"
                    v-if="rushesGroup.best"
                  :class="{
                      'rushesStripBubble': rushesGroup.best.latest_strip.type == 'bubble',
                      'rushesStripPhoto': rushesGroup.best.latest_strip.type == 'image',
                      'rushesStripVideo': rushesGroup.best.latest_strip.type == 'video',
                  }"
                  :style="{'background-image': rushesGroup.best.latest_strip.type == 'image' ? 'url('+ rushesGroup.best.latest_strip.image +')' : ''}"
                  @click="viewRush(rushesGroup.best.id)">
                  <span>{{ rushesGroup.best.title }}</span>
                  <div v-if="rushesGroup.best.latest_strip.type == 'bubble'" class="rushesStripBubbleText">
                      <p>Some text here</p>
                  </div>
                  <div class="claps">
                      <div class="clapsIcon clapped"></div>
                      <span>{{ rushesGroup.best.applauses_count > 0 ? rushesGroup.best.applauses_formatted : '' }}</span>
                  </div>
              </div>
              <div class="rushItem"
                   v-for="rush in rushesGroup.group"
                  :class="{
                      'rushesStripBubble': rush.latest_strip.type == 'bubble',
                      'rushesStripPhoto': rush.latest_strip.type == 'image',
                      'rushesStripVideo': rush.latest_strip.type == 'video',
                  }"
                  :style="{'background-image': rush.latest_strip.type == 'image' ? 'url('+ rush.latest_strip.image +')' : ''}"
                  @click="viewRush(rush.id)">
                  <span>{{ rush.title }}</span>
                  <div v-if="rush.latest_strip.type == 'bubble'" class="rushesStripBubbleText">
                      <p>Some text here</p>
                  </div>
                  <div class="claps">
                      <div class="clapsIcon clapped"></div>
                      <span>{{ rush.applauses_count > 0 ? rush.applauses_formatted : '' }}</span>
                  </div>
              </div>
          </div>
      </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

import {
    _type as rushesType
} from '@rush/modules/rushes'

export default {
    computed: {
        ...mapGetters({
            rushes:   rushesType.getters.list,
            position: rushesType.getters.position,
        }),
    },
    methods: {
        ...mapActions({
            setScrollPosition: rushesType.actions.position,
        }),
        viewRush(rushId) {
            this.$router.push({name: 'rush.view', params: {rushId: rushId}})
        },
        handleScroll() {
            this.setScrollPosition(window.scrollY)
        },
    },
    created () {
        if (this.position > 0) {
            let v = this
            setTimeout(() => {
                window.scroll(0, v.position)
            }, 0)
        }
        window.addEventListener('scroll', this.handleScroll)
    },
    destroyed () {
        window.removeEventListener('scroll', this.handleScroll)
    },
}
</script>
