<template>
  <vue-custom-scrollbar ref="vueCustomScrollbar" :settings="scrollBarSettings" class="filter-menu">
    <div class="inner">
      <div class="box">
        <div class="options-box view">
          <div class="options">
            <input id="tile" type="radio" class="toggle" name="view" :checked="viewType == 'grid'"
                   v-on:click="changeViewType('grid')" value="grid">
            <label for="tile" class="tile"><span></span></label>
            <input id="list" type="radio" class="toggle" name="view" :checked="viewType == 'list'"
                   v-on:click="changeViewType('list')" value="list">
            <label for="list" class="list"><span></span></label>
          </div>
        </div>
        <div class="search" @click="searchBuddy()">
          <input
              type="text"
              autocomplete="off"
              :placeholder="trans('main_search_users')"
              name="searchInput"
              @click="goTo('/search')"
              v-model="searchInput">
          <span class="submit"></span>
        </div>
      </div>
      <div class="box">
        <div class="options-box list-type">
          <div class="options">
            <input dusk="discover-nearby" id="all-buddies" type="radio" class="toggle" name="sort"
                   @click="setMode('nearby')"
                   :checked="filterType === 'nearby'">
            <label for="all-buddies" class="first">{{ trans('all') }}</label>
            <input dusk="discover-recent" id="new-buddies" type="radio" class="toggle" name="sort"
                   @click="setMode('recent')"
                   :checked="filterType === 'recent'">
            <label for="new-buddies" class="middle">{{ trans('new') }}</label>
            <input dusk="discover-favorites" id="favorite-buddies" type="radio" class="toggle" name="sort"
                   @click="setMode('favorites')"
                   :checked="filterType === 'favorites'">
            <label for="favorite-buddies" class="last">{{ trans('favorites') }}</label>
          </div>
        </div>
      </div>
      <div class="box buttons filter-options">
        <div class="filter-option online"
             v-bind:class="{'selected': filterOnline}"
             @click="toggleFilter('filterOnline')"
        >
          <span class="filter-option-label">{{ trans('online') }}</span>
        </div>
        <div class="filter-option photos"
             v-bind:class="{'selected': filterPics}"
             @click="toggleFilter('filterPics')"
        >
          <span class="filter-option-label">{{ trans('pics') }}</span>
        </div>
        <div class="filter-option videos"
             v-bind:class="{'selected': filterVideos}"
             @click="toggleFilter('filterVideos')"
        >
          <span class="filter-option-label">{{ trans('videos') }}</span>
        </div>
      </div>
      <div class="box">
        <div class="headline">{{ trans('position') }}</div>
        <div class="filter-checkbox">
          <div class="checkbox" v-for="(option, key) in options.positionTypes">
            <input :id="`form-checkbox-position-${key}`" type="checkbox" class="toggle" v-model="filterPositionForm" @change="setFilterPositionForm(filterPositionForm)"
                   :value="option">
            <label :id="`form-label-position-${key}`" :for="`form-checkbox-position-${key}`">{{
                transPosition(option)
              }}</label>
          </div>
        </div>
      </div>
      <div class="box" @click="setSlider('age')" style="display: block;">
        <div class="headline">{{ trans('age') }}</div>
        <div>
          <vue-slider
              v-model="age"
              :min="18"
              :max="99"
              id="age"
              class="slider-range"
              :clickable="false"
              @dragging="swipe"
              :disabled="!ageSliderActive"
              v-bind:class="{'selected': ageSliderActive, 'disable-slider': !ageSliderActive}"
              @drag-end="setAge">
            <div class="result" v-html="getFilterValuePrintable('age')"></div>
          </vue-slider>
        </div>
      </div>
      <div class="box" @click="setSlider('height')" style="display: block;">
        <div class="headline">{{ trans('height') }}</div>
        <div>
          <vue-slider
              v-model="height"
              :min="150"
              :max="212"
              id="height"
              class="slider-range"
              :clickable="false"
              @dragging="swipe"
              @drag-end="setHeight"
              v-bind:class="{'selected': heightSliderActive, 'disable-slider': !heightSliderActive && this.userIsPro}"
              :disabled="this.filterDisabled('filterHeight') || !heightSliderActive">
            <div class="result" v-html="getFilterValuePrintable('height')"></div>
          </vue-slider>
        </div>
      </div>
      <div class="box" @click="setSlider('weight')" style="display: block;">
        <div class="headline">{{ trans('weight') }}</div>
        <div>
          <vue-slider
              v-model="weight"
              :min="45"
              :max="150"
              id="weight"
              class="slider-range"
              :clickable="false"
              @dragging="swipe"
              @drag-end="setWeight"
              v-bind:class="{'selected': weightSliderActive, 'disable-slider': !weightSliderActive && this.userIsPro}"
              :disabled="this.filterDisabled('filterWeight') || !weightSliderActive">
            <div class="result" v-html="getFilterValuePrintable('weight')"></div>
          </vue-slider>
        </div>
      </div>
      <div class="box">
        <div class="headline">{{ trans('body') }}</div>
        <div class="filter-checkbox">
          <div class="checkbox" v-for="(option, key) in options.bodyTypes">
            <input :id="`form-checkbox-body-${key}`" type="checkbox" v-if="userIsPro" v-model="filterBodyForm" @change="setFilterBodyForm(filterBodyForm)"
                   :value="option" class="toggle">
            <input :id="`form-checkbox-body-${key}`" type="checkbox" v-else @click.prevent="checkFilter()"
                   class="toggle">
            <label :for="`form-checkbox-body-${key}`">{{ transBody(option) }}</label>
          </div>
        </div>
      </div>
      <div class="box">
        <div class="headline">{{ trans('penis') }}</div>
        <div class="filter-checkbox size">
          <div class="checkbox" v-for="(option, key) in options.penisSizes">
            <input :id="`form-checkbox-penis-${key}`" type="checkbox" v-if="userIsPro" v-model="filterPenisForm" @change="setFilterPenisForm(filterPenisForm)"
                   :value="option" class="toggle">
            <input :id="`form-checkbox-penis-${key}`" type="checkbox" v-else @click.prevent="checkFilter()"
                   class="toggle">
            <label :for="`form-checkbox-penis-${key}`">{{ option }}</label>
          </div>
        </div>
      </div>
      <div class="box">
        <div class="headline">{{ trans('drugs') }}</div>
        <div class="filter-checkbox">
          <div class="checkbox" v-for="(option, key) in options.drugsTypes">
            <input :id="`form-checkbox-drugs-${key}`" type="checkbox" v-if="userIsPro" v-model="filterDrugsForm" @change="setFilterDrugsForm(filterDrugsForm)"
                   :value="option" class="toggle">
            <input :id="`form-checkbox-drugs-${key}`" type="checkbox" v-else @click.prevent="checkFilter()"
                   class="toggle">
            <label :for="`form-checkbox-drugs-${key}`">{{ transDrugs(option) }}</label>
          </div>
        </div>
      </div>
      <div class="box">
        <div class="headline">{{ trans('hiv') }}</div>
        <div class="filter-checkbox">
          <div class="checkbox" v-for="(option, key) in options.hivTypes">
            <input :id="`form-checkbox-hiv-${key}`" type="checkbox" v-if="userIsPro" v-model="filterHivForm" @change="setFilterHivForm(filterHivForm)"
                   :value="option" class="toggle">
            <input :id="`form-checkbox-hiv-${key}`" type="checkbox" v-else @click.prevent="checkFilter()"
                   class="toggle">
            <label :for="`form-checkbox-hiv-${key}`">{{ transHiv(option) }}</label>
          </div>
        </div>
      </div>
    </div>
  </vue-custom-scrollbar>
</template>

<script>
import {mapState, mapActions, mapGetters} from 'vuex';

import CustomReveal from '@buddy/views/widgets/CustomReveal.vue';
import Alert from '@buddy/views/widgets/Alert.vue'

import discoverModule from '@discover/module/store/type';
import {storedFormFilters} from '@discover/lib/helpers';

/* Vue slider  */
import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/default.css'

// custom scrollbar
import vueCustomScrollbar from 'vue-custom-scrollbar'
import "vue-custom-scrollbar/dist/vueScrollbar.css"

export default {
  mixins: [require('@general/lib/mixin').default],
  props: ['disableSwipe'],
  data() {
    return {
      age: [18, 99],
      height: [150, 212],
      weight: [45, 150],
      searchInput: null,
      viewType: null,
      filterOnline: null,
      filterPics: null,
      filterVideos: null,
      filterAge: null,
      filterPosition: null,
      filterHeight: null,
      filterWeight: null,
      filterBody: null,
      filterPenis: null,
      filterDrugs: null,
      filterHiv: null,
      filterType: null,

      filterAgeValues: null,
      filterPositionValues: null,
      filterHeightValues: null,
      filterWeightValues: null,
      filterBodyValues: null,
      filterPenisValues: null,
      filterDrugsValues: null,
      filterHivValues: null,

      filterPositionForm: [],
      filterHeightForm: [],
      filterWeightForm: [],
      filterBodyForm: [],
      filterPenisForm: [],
      filterDrugsForm: [],
      filterHivForm: [],

      ageRevealVisible: false,
      positionRevealVisible: false,
      heightRevealVisible: false,
      weightRevealVisible: false,
      bodyRevealVisible: false,
      penisRevealVisible: false,
      drugsRevealVisible: false,
      hivRevealVisible: false,

      sliderAge: null,
      sliderWeight: null,
      sliderHeight: null,

      ageSliderActive: false,
      heightSliderActive: false,
      weightSliderActive: false,

      swipeDisable: false,

      scrollBarSettings: {
        suppressScrollY: false,
        suppressScrollX: true
      }
    }
  },
  components: {
    CustomReveal,
    Alert,
    VueSlider,
    vueCustomScrollbar
  },
  methods: {
    ...mapActions([
      'requirementsAlertShow'
    ]),
    swipe() {
      if (!this.swipeDisable) {
        this.$emit('disableSwipe', !this.swipeDisable);
      }

      this.swipeDisable = true;
    },
    setFilterData() {
        let filter = this.$store.getters[discoverModule.getters.filter.get]
        let allFilters = storedFormFilters
        let sliderFiltersKeys = ['filterAge', 'filterWeight', 'filterHeight']

        for (let filterKey in allFilters) {
            let filterValue = filter(`${filterKey}Values`)

            if (sliderFiltersKeys.includes(filterKey) && filter(filterKey) !== null) {

                let sliderData = filterValue?.split(',')

                switch (filterKey) {
                  case 'filterAge':
                    if (sliderData) {
                      this.age = sliderData;
                    }
                    this.ageSliderActive = true;
                    break;
                  case 'filterWeight':
                    if (this.userIsPro) {
                      if (sliderData) {
                        this.weight = sliderData;
                      }
                      this.weightSliderActive = true;
                    }
                    break;
                  case 'filterHeight':
                    if (this.userIsPro) {
                      if (sliderData) {
                        this.height = sliderData;
                      }
                      this.heightSliderActive = true;
                    }
                    break;
                }
            } else if (filterValue) {
              this[filterKey] = filterValue;
              this[`${filterKey}Form`] = filterValue.split(',')
              this[`${filterKey}Values`] = filterValue
            } else {
              this[filterKey] = filter(filterKey)
            }
        }
    },
    deactivateSlider(key, refresh) {
      this.$store.dispatch(discoverModule.actions.filter.remove, {
        key,
        refresh
      });
    },
    setFilter(key, value, refresh) {
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key,
        value,
        refresh
      });
    },
    setAge() {
        this.setFilter('filterAge', this.ageSliderActive, false);

        const age = this.age.join(',');
        this.setFilter('filterAgeValues', age, true)

        setTimeout(() => {
          this.swipeDisable = false;
          this.$emit('disableSwipe', false);
        })
    },
    setHeight() {
        this.setFilter('filterHeight', this.heightSliderActive, false);

        const height = this.height.join(',');
        this.setFilter('filterHeightValues', height, true)

        setTimeout(() => {
          this.swipeDisable = false;
          this.$emit('disableSwipe', false);
        }, 100);
    },
    setWeight() {
      if (this.weightSliderActive) {
        this.setFilter('filterWeight', this.weightSliderActive, false);

        const weight = this.weight.join(',');
        this.setFilter('filterWeightValues', weight, true)

        setTimeout(() => {
          this.swipeDisable = false;
          this.$emit('disableSwipe', false);
        }, 100);
      }
    },
    changeViewType(type) {
      this.viewType = type
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'viewType',
        value: type,
        queueRefresh: false
      })
    },
    setMode(mode) {
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterType',
        value: mode,
        refresh: true
      })
    },
    searchBuddy() {
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterName',
        value: this.searchInput,
        refresh: true
      })

      this.goTo('/search');
    },
    resetSearchUsers() {
      this.searchInput = ''
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterName',
        value: '',
        queueRefresh: true
      })
    },
    checkIfUserPro() {
      if (!this.userIsPro) {
        this.requirementsAlertShow('filters')
      }
    },
    checkFilter() {
      if (!this.userIsPro) {
        this.requirementsAlertShow('filters')
      }
    },
    setSlider(sliderName) {
      if (this.swipeDisable) {
        return;
      }
      const userIsPro = this.userIsPro;

      if (!userIsPro && sliderName !== 'age') {
        this.requirementsAlertShow('filters');
        return;
      }

      let activeSlider, filterKey, filterKeyValues;

      if (sliderName === 'age') {
        activeSlider = !this.ageSliderActive;
        this.ageSliderActive = activeSlider;
      } else if (userIsPro && sliderName === 'height') {
        activeSlider = !this.heightSliderActive;
        this.heightSliderActive = activeSlider;
      } else if (userIsPro && sliderName === 'weight') {
        activeSlider = !this.weightSliderActive;
        this.weightSliderActive = activeSlider;
      }

      if (!activeSlider) {
        if (sliderName === 'age') {
          filterKeyValues = 'filterAgeValues';
          filterKey = 'filterAge';
        } else if (sliderName === 'height') {
          filterKeyValues = 'filterHeightValues';
          filterKey = 'filterHeight';
        } else if (sliderName === 'weight') {
          filterKeyValues = 'filterWeightValues';
          filterKey = 'filterWeight';
        }

        this.deactivateSlider(filterKey, true);
        this.setFilter(filterKey, activeSlider,true)
      } else {
        if (sliderName === 'age') {
          this.setAge();
        } else if (sliderName === 'height') {
          this.setHeight();
        } else if (sliderName === 'weight') {
          this.setWeight();
        }
      }

      this.$emit('disableSwipe', false);
    },
    toggleFilter(filterKey, newValue) {
      if (!this.userIsPro && this.filterDisabled(filterKey)) {
        this.requirementsAlertShow('filters')
      } else {
        if (newValue === undefined) {
          newValue = !this[filterKey]
        }
        this[filterKey] = newValue
        this.$store.dispatch(discoverModule.actions.filter.set, {
          key: filterKey,
          value: this[filterKey],
          queueRefresh: true
        })
      }
    },
    getFilterValuePrintable(filterKey) {
      if (!this.options.positionTypes) {
        return ''
      }

      if (filterKey === 'age') {
        return this[filterKey][0] + " - " + this[filterKey][1]
      } else if (filterKey === 'height') {
        return this.formatHeight(this[filterKey][0]) + " - " + this.formatHeight(this[filterKey][1]);
      } else if (filterKey === 'weight') {
        return this.formatWeight(this[filterKey][0]) + " - " + this.formatWeight(this[filterKey][1]);
      } else if (filterKey === 'filterPositionValues') {
        const filterArray = this[filterKey].split(',')
        return Object.keys(this.options.positionTypes).filter(v => filterArray.includes(v)).map(v => this.transPosition(v)).join(', ')
      } else if (filterKey === 'filterBodyValues') {
        const filterArray = this[filterKey].split(',')
        return Object.keys(this.options.bodyTypes).filter(v => filterArray.includes(v)).map(v => this.transBody(v)).join(', ')
      } else if (filterKey === 'filterPenisValues') {
        const filterArray = this[filterKey].split(',')
        return Object.keys(this.options.penisSizes).filter(v => filterArray.includes(v)).join(', ')
      } else if (filterKey === 'filterDrugsValues') {
        const filterArray = this[filterKey].split(',')
        return Object.keys(this.options.drugsTypes).filter(v => filterArray.includes(v)).map(v => this.transDrugs(v)).join(', ')
      } else if (filterKey === 'filterHivValues') {
        const filterArray = this[filterKey].split(',')
        return Object.keys(this.options.hivTypes).filter(v => filterArray.includes(v)).map(v => this.transHiv(v)).join(', ')
      } else if (filterKey === 'filterHivValues') {
        const filterArray = this[filterKey].split(',')
        return Object.keys(this.options.hivTypes).filter(v => filterArray.includes(v)).map(v => this.transHiv(v)).join(', ')
      } else {
        return `not done yet for ${filterKey}`
      }
    },
    restoreFilters() {
        let allFilters = storedFormFilters;
        let filter = this.$store.getters[discoverModule.getters.filter.get];
        let filterKeyValues, filterValues;

        allFilters = _.omit(allFilters, ['filterAge', 'filterHeight', 'filterWeight'])

        for (let filterKey in allFilters) {
            if (allFilters[filterKey].ranged) {
                filterKeyValues = `${filterKey}Values`;
                filterValues = filter(filterValues);

                if (!filterValues) {
                  this.$store.dispatch(discoverModule.actions.filter.set, {
                    key: filterKey,
                    value: false,
                    refresh: false
                  });
                }
            }
        }
    },

    checkProFilterAfterLoad() {
      this.setFilterPenisForm(this.filterPenisForm);
      this.setFilterPositionForm(this.filterPositionForm);
      this.setFilterBodyForm(this.filterBodyForm);
      this.setFilterHivForm(this.filterHivForm);
      this.setFilterDrugsForm(this.filterDrugsForm);
    },

    setFilterPenisForm(value) {
      let boo = value.length !== 0;
      this.setFilter('filterPenis', boo, false)

      let data = value.join(',');
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterPenisValues',
        value: data,
        refresh: true
      });
    },

    setFilterPositionForm(value) {
      let boo = value.length !== 0;

      this.setFilter('filterPosition', boo, false)

      let position = value.join(',');
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterPositionValues',
        value: position,
        refresh: true
      });
    },

    setFilterBodyForm(value) {
      let boo = value.length !== 0;
      this.setFilter('filterBody', boo, false)

      let body = value.join(',');
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterBodyValues',
        value: body,
        refresh: true
      });
    },

    setFilterHivForm(value) {
      let boo = value.length !== 0;
      this.setFilter('filterHiv', boo, false)

      let hiv = value.join(',');
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterHivValues',
        value: hiv,
        refresh: true
      });
    },

    setFilterDrugsForm(value) {
      let boo = value.length !== 0;
      this.setFilter('filterDrugs', boo, false)

      let data = value.join(',');
      this.$store.dispatch(discoverModule.actions.filter.set, {
        key: 'filterDrugsValues',
        value: data,
        refresh: true
      });
    }
  },

  watch: {
    userIsPro(value) {
      if (value !== null) {
        this.restoreFilters();
        this.setFilterData();
        this.checkProFilterAfterLoad();
      }
    },
  },
  computed: {
    ...mapState({
      options: 'profileOptions'
    }),
    ...mapGetters({
      filterDisabled: discoverModule.getters.filter.isDisabled,
      getDefaultFilterValues: discoverModule.getters.filter.default
    }),
  },
  activated() {
    this.setFilterData()
    this.$refs.vueCustomScrollbar.$forceUpdate()
  }
}
</script>
<style>
.disable-slider {
  opacity: 1 !important;
}
</style>