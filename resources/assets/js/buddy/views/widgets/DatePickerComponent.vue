<template>
    <input v-if="useNativeIf" :max="max" :min="min" @change="inputChangeMobile" :value="value" type="date" :placeholder="placeholder" v-bind="$attrs" :class="inputClass" v-validate="vValidate" :name="name" :id="id" :data-vv-as="dataVvAs" />

    <div v-else class="date-container" :class="{'centered': centered, 'toRight': toRight}">
        <input class="datepicker-input" type="text" readonly @focus="showCalendar" v-model="outputDate" :placeholder="placeholder" v-bind="$attrs" :id="id+'-output'" :class="inputClass" />
        <input type="hidden" @change="inputChangeDesktop" v-validate="vValidate" v-model="modelValue" :name="name" :id="id" :data-vv-as="dataVvAs" />

        <div class="date-select" v-show="calendarOpen">
            <div class="popup" onwheel="event.preventDefault()">
                <span class="tip"></span>
                <div class="select day" @wheel="dayWheel" @touchstart="fingerStart" @touchmove="fingerMoved('day', $event)">
                    <a @click="dayUp" class="btn-arrow btn-up"><i class="icon icon-up"></i></a>
                    <div>
                        <span class="num">{{ formDay }}</span>
                        <span class="text">{{ formDayName }}</span>
                        </div>
                    <a @click="dayDown" class="btn-arrow btn-down"><i class="icon icon-down"></i></a>
                </div>
                <div class="select month" @wheel="monthWheel" @touchstart="fingerStart" @touchmove="fingerMoved('month', $event)">
                    <a @click="monthUp" class="btn-arrow btn-up"><i class="icon icon-up"></i></a>
                    <div>
                        <span class="num">{{ formMonth }}</span>
                        <span class="text">{{ formMonthName }}</span>
                        </div>
                    <a @click="monthDown" class="btn-arrow btn-down"><i class="icon icon-down"></i></a>
                </div>
                <div class="select year" @wheel="yearWheel" @touchstart="fingerStart" @touchmove="fingerMoved('year', $event)">
                    <a @click="yearUp" class="btn-arrow btn-up"><i class="icon icon-up"></i></a>
                    <div>
                        <span class="num">{{ formYear }}</span>
                        <span class="text">Year</span>
                        </div>
                    <a @click="yearDown" class="btn-arrow btn-down"><i class="icon icon-down"></i></a>
                </div>
                <!--<img src="/assets/img/finger-scroll.png" alt="" />-->
                <div class="buttons">
                    <a @click="hideCalendar" class="btn-cancel"><i class="fa fa-times"></i></a>
                    <a @click="clearDateButton" class="btn-del"><i class="fa fa-trash"></i></a>
                    <a @click="acceptButton" class="btn-ok"><i class="fa fa-check"></i></a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
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
            'useNativeIf', //disable if condition
            'max', //max calendar date
            'min', //min calendar date
        ],
        inject: ['$validator'],
        data() {
            return {
                calendarOpen: false, //if popup calendar is visible
                maxDate: '', //max popup calendar date
                minDate: '', //min popup calendar date
                outputElement: false, //visible input element - which displays formatted date
                hiddenElement: false, //hidden input element - which contains date in format YYYY-MM-DD
                selectDate: false, //date used in popup calendar
                calendarLanguage: this.language ? this.language : 'en', //errors and dates language
                outputFormat: this.formatDate ? this.formatDate : 'DD MMMM YYYY', //format of date displayed in visible input element
                modelValue: this.value, //value of hidden input, is needed for validation to work
                lastClientY: false, //touchmove event
            }
        },
        inheritAttrs: false,
        watch: {
            value(newVal) {
                this.modelValue = newVal;
            }
        },
        created() {
            moment.locale(this.calendarLanguage);
            if (this.max) {
                this.maxDate = moment(this.max);
            }

            if (this.min) {
                this.minDate = moment(this.min);
            }

            this.resetSelectDate();
        },
        mounted() {
            if (this.useNativeIf) {
                return;
            }

            this.outputElement = this.$el.querySelector(`#${this.id}-output`);

            this.hiddenElement = this.$el.querySelector(`#${this.id}`);

            let newTop = this.outputElement.offsetHeight+12;

            this.$el.querySelector('.date-select').style.top = newTop + 'px';
        },
        computed: {
            outputDate() {
                if (this.value) {
                    return moment(this.value).format(this.outputFormat);
                }

                return ''
            },
            formDay() {
                return this.selectDate.format('DD')
            },
            formMonth() {
                return this.selectDate.format('MM')
            },
            formYear() {
                return this.selectDate.format('YYYY')
            },
            formDayName() {
                return this.selectDate.format('dddd')
            },
            formMonthName() {
                return this.selectDate.format('MMMM')
            }
        },
        methods: {
            fingerStart(e) {
                this.lastClientY = e.touches[0].clientY;
            },
            fingerMoved(type, e) {
                if (e.touches[0].clientY > this.lastClientY) {
                    if (type == 'month') {
                        this.monthDown()
                    }
                    else if (type == 'day') {
                        this.dayDown()
                    }
                    else if (type == 'year') {
                        this.yearDown()
                    }
                } else if (e.touches[0].clientY < this.lastClientY) {
                    if (type == 'month') {
                        this.monthUp()
                    }
                    else if (type == 'day') {
                        this.dayUp()
                    }
                    else if (type == 'year') {
                        this.yearUp()
                    }
                }

                this.lastClientY = e.touches[0].clientY;
            },
            addOutsideClickListener() {
                document.addEventListener('click', this.clickOutside, false)
            },
            removeOutsideClickListener() {
                document.removeEventListener('click', this.clickOutside, false)
            },
            clickOutside(e) {
                if (this.$el && !this.$el.contains(e.target)) {
                    this.hideCalendar(e)
                }
            },

            inputChangeMobile(e) {
                this.$emit('change', e);

                this.$emit('input', this.$el.value);
            },
            inputChangeDesktop(e) {
                setTimeout(() => {
                    this.$emit('change', e);
                }, 0)
            },

            resetSelectDate() {
                if (this.value) {
                    this.assignSelectDate(moment(this.value));
                } else {
                    this.assignSelectDate(moment());
                }
            },
            assignSelectDate(newDate) {
                if (typeof this.maxDate == 'object' && this.maxDate.isBefore(newDate)) {
                    newDate = this.maxDate;
                }

                if (typeof this.minDate == 'object' && this.minDate.isAfter(newDate)) {
                    newDate = this.minDate;
                }

                this.selectDate = newDate;
            },

            showCalendar() {
                if (this.calendarOpen) {
                    return;
                }

                this.addOutsideClickListener();

                this.resetSelectDate();

                this.calendarOpen = true;
            },
            hideCalendar(e) {
                e.preventDefault();

                this.removeOutsideClickListener();

                this.calendarOpen = false;
            },

            acceptButton(e) {
                this.$emit('input', this.selectDate.format('YYYY-MM-DD'));

                this.hiddenElement.dispatchEvent(new Event('change'))

                this.hideCalendar(e);
            },
            clearDateButton(e) {
                this.$emit('input', '');

                this.hiddenElement.dispatchEvent(new Event('change'))

                this.hideCalendar(e);
            },

            dayUp() {
                this.assignSelectDate(moment(this.selectDate).add(1, 'days'))

                this.outputElement.focus()
            },
            dayDown() {
                this.assignSelectDate(moment(this.selectDate).subtract(1, 'days'))

                this.outputElement.focus()
            },
            monthUp() {
                this.assignSelectDate(moment(this.selectDate).add(1, 'months'))

                this.outputElement.focus()
            },
            monthDown() {
                this.assignSelectDate(moment(this.selectDate).subtract(1, 'months'))

                this.outputElement.focus()
            },
            yearUp() {
                this.assignSelectDate(moment(this.selectDate).add(1, 'years'))

                this.outputElement.focus()
            },
            yearDown() {
                this.assignSelectDate(moment(this.selectDate).subtract(1, 'years'))

                this.outputElement.focus()
            },
            dayWheel(e) {
                if (e.deltaY < 0) {
                    this.dayUp()
                }
                else if (e.deltaY > 0) {
                    this.dayDown()
                }
            },
            monthWheel(e) {
                if (e.deltaY < 0) {
                    this.monthUp()
                }
                else if (e.deltaY > 0) {
                    this.monthDown()
                }
            },
            yearWheel(e) {
                if (e.deltaY < 0) {
                    this.yearUp()
                }
                else if (e.deltaY > 0) {
                    this.yearDown()
                }
            }
        }
    }
</script>

<style>
    .datepicker-input::placeholder {
        color: #FAFCFA !important;
        text-align: center;
    }
    .date-container {
        position: relative;
    }
    .date-select {
        position: absolute;
    }
    .date-container.centered .date-select {
        left: 50%;
        margin: 0 0 0 -153px;
    }
    .date-container.toRight .date-select {
        right: 0;
        width: 308px;
    }
    .date-select .popup {
        position: absolute;
        top: 0;
        z-index: 15;
    }
    .date-select .tip {
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
    .date-select .tip:before {
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
    .date-container.centered .date-select .tip {
        left: 50%;
        margin: 0 0 0 -10px;
    }
    .date-container.toRight .date-select .tip {
        left: auto;
        right: 12px;
    }
    .date-select .select {
        width: 100px;
        height: 100px;
        float: left;
        margin-right: 1px;
        position: relative;
        cursor: default;
        touch-action: none;
    }
    .date-select .select.year {
        margin-right: 0;
    }
    .date-select .select span {
        display: block;
        margin: 0 10px 0;
    }
    .date-select .select span.num {
        font-size: 34px;
        line-height: 45px;
    }
    .date-select .select span.text {
        font-size: 12px;
        text-transform: uppercase;
        line-height: 15px;
    }
    .date-select .select:hover {
        background: #00F000;
        color: #1D1D1D;
    }
    .date-select .select:hover span {
        color: #1D1D1D !important;
    }
    .date-select .select a.btn-arrow {
        position: absolute;
    }
    .date-select .select a.btn-arrow.btn-up {
        left: 0;
        top: 0;
        width: 100%;
        height: 20px;
    }
    .date-select .select a.btn-arrow.btn-down {
        left: 0;
        bottom: 0;
        width: 100%;
        height: 20px;
    }
    .date-select .select .icon {
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
    .date-select .select .icon.icon-up {
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-bottom: 6px solid #ccc;
    }
    .date-select .select .icon.icon-down {
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #ccc;
    }
    .date-select .select:hover .icon {
        /* opacity */
        filter: alpha(opacity=100);
        opacity: 1;
    }
    .date-select .select:hover .icon.icon-up {
        border-bottom: 6px solid #004646;
    }
    .date-select .select:hover .icon.icon-down {
        border-top: 6px solid #004646;
    }
    .date-select .select a.btn-arrow:hover .icon.icon-up {
        border-bottom-color: #004646;
    }
    .date-select .select a.btn-arrow:hover .icon.icon-down {
        border-top-color: #004646;
    }
    .date-select .buttons a {
        font-size: 18px;
    }
    .date-select .buttons a.btn-ok {
        margin-left: 1px;
    }
    .date-select .buttons a.btn-del:hover {
        background: #D95005;
    }
    .date-select .buttons a.btn-ok:hover {
        background: #69B22C;
    }
    .date-select .buttons a.btn-cancel:hover {
        background: #D95005;
    }
</style>
