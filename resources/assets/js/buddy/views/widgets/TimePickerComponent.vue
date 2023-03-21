<template>
  <div class="time-container" :class="{'centered': centered}">
    <input class="datepicker-input"  :placeholder="placeholder" type="text" readonly @focus="showTimePicker" v-model="outputTime" v-bind="$attrs" :id="id+'-output'" :class="inputClass" />
    <input type="hidden" @change="" v-validate="vValidate" v-model="modelValue" :name="name" :id="id" :data-vv-as="dataVvAs" />

    <div class="time-select" v-show="timePickerOpen">
      <div class="popup" onwheel="event.preventDefault()">
        <span class="tip"></span>
        <div class="select day" @wheel="hoursWheel" @touchstart="fingerStart" @touchmove="fingerMoved('hours', $event)">
          <a @click="addHour" class="btn-arrow btn-up"><i class="icon icon-up"></i></a>
          <div>
            <span class="num">{{ hours }}</span>
            <span class="text">Hours</span>
          </div>
          <a @click="removeHour" class="btn-arrow btn-down"><i class="icon icon-down"></i></a>
        </div>
        <div class="select month" @wheel="minutesWheel" @touchstart="fingerStart" @touchmove="fingerMoved('minutes', $event)">
          <a @click="addMinutes" class="btn-arrow btn-up"><i class="icon icon-up"></i></a>
          <div>
            <span class="num">{{ minutes }}</span>
            <span class="text">Minutes</span>
          </div>
          <a @click="removeMinutes" class="btn-arrow btn-down"><i class="icon icon-down"></i></a>
        </div>
        <!--<img src="/assets/img/finger-scroll.png" alt="" />-->
        <div class="buttons">
          <a @click="closeTimePicker" class="btn-cancel"><i class="fa fa-times"></i></a>
          <a @click="clearTimePicker" class="btn-del"><i class="fa fa-trash"></i></a>
          <a @click="acceptTime" class="btn-ok"><i class="fa fa-check"></i></a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "TimePickerComponent",
  props: [
    'centered',
    'toRight',
    'value', //value of hidden input in format YYYY-MM-DD
    'name', //name of validated element
    'id',  //id of validated element
    //optional
    'formatDate', //output format date
    'language', //language: en, de, etc
    'vValidate', //v-validate rules
    'inputClass', //class applied to the output element
    'placeholder', //displayed element placeholder
    'dataVvAs', //data-vv-as from vee-validate
  ],
  inject: ['$validator'],
  data() {
    return {
      timePickerOpen: false, //if popup time picker is visible
      outputElement: false, //visible input element - which displays formatted date
      hiddenElement: false, //hidden input element - which contains time in format HH:mm
      outputFormat: this.formatDate ? this.formatDate : 'HH:mm', //format of time displayed in visible input element
      modelValue: this.value, //value of hidden input, is needed for validation to work
      lastClientY: false, //touchmove event,
      selectedTime: null,
    }
  },
  created() {
      this.setTime();
  },
  watch: {
    value(newVal) {
      this.modelValue = newVal;
    }
  },
  methods: {
    acceptTime(e) {
      this.$emit('input', this.selectedTime.format('HH:mm'));

      this.closeTimePicker(e);
    },
    clearTimePicker(e) {
      this.$emit('input', '');

      this.closeTimePicker(e);
    },
    addOutsideClickListener() {
      document.addEventListener('click', this.clickOutside, false)
    },
    removeOutsideClickListener() {
      document.removeEventListener('click', this.clickOutside, false)
    },
    clickOutside(e) {
      if (this.$el && !this.$el.contains(e.target)) {
        this.closeTimePicker(e)
      }
    },
    fingerStart(e) {
      this.lastClientY = e.touches[0].clientY;
    },
    fingerMoved(type, e) {
      if (e.touches[0].clientY > this.lastClientY) {
        if (type == 'hours') {
          this.removeHour()
        }
        else if (type == 'minutes') {
          this.removeMinutes()
        }
      } else if (e.touches[0].clientY < this.lastClientY) {
        if (type == 'hours') {
          this.addHour()
        }
        else if (type == 'minutes') {
          this.addMinutes()
        }
      }

      this.lastClientY = e.touches[0].clientY;
    },
    showTimePicker() {
      this.addOutsideClickListener();

      this.timePickerOpen = true;
    },
    closeTimePicker(e) {
      e.preventDefault();

      this.removeOutsideClickListener();

      this.timePickerOpen = false;
    },
    hoursWheel(e) {
      if (e.deltaY < 0) {
        this.addHour()
      }
      else if (e.deltaY > 0) {
        this.removeHour()
      }
    },
    minutesWheel(e) {
      if (e.deltaY < 0) {
        this.addMinutes()
      }
      else if (e.deltaY > 0) {
        this.removeMinutes()
      }
    },
    addHour() {
        this.selectedTime = moment(this.selectedTime).add(1, 'hours');
    },
    removeHour() {
        this.selectedTime = moment(this.selectedTime).subtract(1, 'hours');
    },
    addMinutes() {
        let minutes = parseInt(this.minutes);

        if ((minutes + 5) !== 60) {
            this.selectedTime = moment(this.selectedTime).add(5, 'minutes');
        } else {
            this.selectedTime = moment(`${this.hours}:00`, 'HH:mm');
        }

    },
    removeMinutes() {
        let minutes = parseInt(this.minutes);

        if ((minutes - 5) < 0) {
            this.selectedTime = moment(`${this.hours}:55`, 'HH:mm');
        } else {
          this.selectedTime = moment(this.selectedTime).subtract(5, 'minutes');
        }
    },
    setTime() {
        if (this.value) {
          this.selectedTime = moment(this.value, 'HH:mm');
        } else {
          this.selectedTime = moment('00:00', 'HH:mm');
        }
    }
  },
  computed: {
      outputTime() {
          if (this.value) {
              return this.selectedTime.format('HH:mm');
          }

          return '';
      },
      hours() {
          return this.selectedTime.format("HH");
      },
      minutes() {
          return this.selectedTime.format('mm');
      }
  }
}
</script>

<style scoped>
.datepicker-input::placeholder {
  color: #FAFCFA !important;
  text-align: center;
}
.time-container {
  position: relative;
}
.time-select {
  position: absolute;
}
.time-container.centered .time-select {
  left: 0;
  right: 0;
  margin: auto;
}
.time-container.toRight .time-select {
  right: 0;
  width: 308px;
}
.time-select .popup {
  position: absolute;
  top: 0;
  z-index: 15;
}
.time-select .tip {
  position: absolute;
  left: 12px;
  top: -12px;
  z-index: 5;
  width: 0;
  height: 0;
  border-left: 10px solid transparent;
  border-right: 10px solid transparent;
  border-bottom: 12px solid #004646;
}
.time-select .tip:before {
  content: ' ';
  position: absolute;
  left: -8px;
  top: 2px;
  z-index: 20;
  width: 0;
  height: 0;
  border-left: 8px solid transparent;
  border-right: 8px solid transparent;
  border-bottom: 10px solid #004646;
}
.time-container.centered .time-select .tip {
  left: 50%;
  margin: 0 0 0 -10px;
}
.time-container.toRight .time-select .tip {
  left: auto;
  right: 12px;
}
.time-select .select {
  width: 100px;
  height: 100px;
  float: left;
  margin-right: 1px;
  position: relative;
  cursor: default;
  touch-action: none;
}
.time-select .select.year {
  margin-right: 0;
}
.time-select .select span {
  display: block;
  margin: 0 10px 0;
}
.time-select .select span.num {
  font-size: 34px;
  line-height: 45px;
}
.time-select .select span.text {
  font-size: 12px;
  text-transform: uppercase;
  line-height: 15px;
}
.time-select .select:hover {
  background: #00F000;
  color: #1D1D1D;
}
.time-select .select:hover span {
  color: #1D1D1D !important;
}
.date-select .select a.btn-arrow {
  position: absolute;
}
.time-select .select a.btn-arrow.btn-up {
  left: 0;
  top: 0;
  width: 100%;
  height: 20px;
}
.time-select .select a.btn-arrow.btn-down {
  left: 0;
  bottom: 0;
  width: 100%;
  height: 20px;
}
.time-select .select .icon {
  display: block;
  margin: 7px auto;
  width: 0;
  height: 0;
  -webkit-transition: opacity 0.3s;
  -moz-transition: opacity 0.3s;
  -ms-transition: opacity 0.3s;
  -o-transition: opacity 0.3s;
  transition: opacity 0.3s;
}
.time-select .select .icon.icon-up {
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-bottom: 6px solid #ccc;
}
.time-select .select .icon.icon-down {
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 6px solid #ccc;
}
.time-select .select:hover .icon {
  /* opacity */
  filter: alpha(opacity=100);
  opacity: 1;
}
.time-select .select:hover .icon.icon-up {
  border-bottom: 6px solid #004646;
}
.time-select .select:hover .icon.icon-down {
  border-top: 6px solid #004646;
}
.time-select .select a.btn-arrow:hover .icon.icon-up {
  border-bottom-color: #004646;
}
.time-select .select a.btn-arrow:hover .icon.icon-down {
  border-top-color: #004646;
}
.time-select .buttons a {
  font-size: 18px;
}
.time-select .buttons a.btn-ok {
  margin-left: 1px;
}
.time-select .buttons a.btn-del:hover {
  background: #D95005;
}
.time-select .buttons a.btn-ok:hover {
  background: #69B22C;
}
.time-select .buttons a.btn-cancel:hover {
  background: #D95005;
}
</style>