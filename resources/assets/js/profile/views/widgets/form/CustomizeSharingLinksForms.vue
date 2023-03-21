<template>
  <div>

    <div class="box">
      <div v-if="sharingLinks.length">
        <ul class="caption-sharing-links">
          <li>{{ trans('date') }}</li>
          <li>{{ trans('status') }}</li>
        </ul>

        <div v-for="(link, index) in sharingLinks" ref="sharingLinks">


          <div class="row sensitive">
            <div class="title pointer">

              <tooltip placement="top-left" mode="none">
<!--       TODO: MODE SHOULD BE CLICK, NONE MEANS DISABLED FOR NOW         -->
                <div slot="outlet">
                  <div class="img" :style="{'background': `url('`+ link.thumbnail +`') no-repeat center / cover`}"  @click="goToLink(sharingDomain+'/'+link.sharing_url.url)"></div>
                  <div class="info-sharing-url">
                    <p @click="goToLink(sharingDomain+'/'+link.sharing_url.url)">{{ sharingDomain+'/'+link.sharing_url.url }}</p>
<!--                    <span>Click here to customize</span>-->
                  </div>
                </div>
                <div slot="tooltip" :class="{specialToolip:index == sharingLinks.length-1}">
                  <form action="" @submit.prevent="saveSettingSharingLink($event)">
                    <div class="sharingWarning" v-if="!userIsPro" v-html="trans('you_need_to_be_a_pro_for_editing')"></div>

                    <input type="hidden" name="link_id" :value="link.sharing_url.id">

                    <span class="padding-bottom-1">{{ trans('expire_at_date') }}</span>
                    <DatePickerComponent
                        name="expire_at"
                        :id="'expireAt' + link.sharing_url.id"
                        :value="link.sharing_url.expire_at | formatDate"
                        v-model="link.sharing_url.expire_at"
                        v-bind:v-validate="{required: true, date_format: 'yyyy-MM-dd'}"
                        v-bind:input-class="{'form-control': true, 'datepicker-input-reg': true}"
                        v-bind:centered="isMobile"
                        placeholder="Click here to set up the date"
                        inputClass="customizeSharingLinksDate"
                        :disabled="!userIsPro"
                        required
                    ></DatePickerComponent>

                    <span class="padding-bottom-1">{{ trans('expire_at_time') }}</span>
                    <TimePickerComponent
                        required
                        :value="link.sharing_url.hours"
                        :modelValue="link.sharing_url.hours"
                        name="time"
                        v-model="link.sharing_url.hours"
                        :id="'expireAtTime' + link.sharing_url.id"
                        v-bind:centered="true"
                        v-bind:toRight="true"
                        v-bind:data-vv-as="trans('time')"
                        v-bind:v-validate="{required: true, date_format: 'HH:mm'}"
                        placeholder="Click here to set up the time"
                        inputClass="customizeSharingLinksTime"
                        :disabled="!userIsPro"
                    >
                    </TimePickerComponent>
                    <span class="padding-bottom-1">{{ trans('views_limit') }}</span>
                    <input type="number" name="views_limit"
                           placeholder="9999"
                           :value="link.sharing_url.views_limit > 0 ? link.sharing_url.views_limit : ''"
                           :disabled="!userIsPro">
                    <button class="btn" :disabled="!userIsPro">{{ trans('save') }}</button>
                  </form>
                </div>
              </tooltip>
            </div>
            <div class="field">
              <div class="toggle-switch">
                <input type="checkbox"
                       v-model="link.sharing_url.status"
                       true-value="active"
                       false-value="disabled"
                       :value="link.sharing_url.status == 'active' ? 1 : 0"
                       @click="updateLink(link.sharing_url.id, $event)"
                       :id="'changeStatusLink' + link.sharing_url.id"
                       name="change_status_link">
                <label :for="'changeStatusLink' + link.sharing_url.id">
                  <span class="toggle-track"></span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div style="margin-bottom: 50px;"></div>
      </div>
      <div v-else>
        <div class="sharingWarning">{{ trans('you_dont_have_any_links') }}</div>
      </div>
    </div>
  </div>
</template>

<style>
.specialToolip form {
  margin-bottom: 50px;
}
.info-sharing-url p {
  margin:0 0 3px 0;
  font-size: 12px;
}
.info-sharing-url span {
  font-size:11px;
  color:grey;
}

.customizeSharingLinksDate::placeholder,
.customizeSharingLinksTime::placeholder {
  color:white;
}

.sharingWarning {
  padding: 20px;
  background-color: #fff3cd; /* Yellow */
  border:1px solid #ffeeba;
  color: black;
  margin-bottom: 15px;
}

.sharing-links form input {
  text-align: left;
  background: #004646 !important;
  color:white;
  border:none;
  border-radius:7px;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
.sharing-links .datepicker-input {
  text-align: left;
}

.sharing-links form .datepicker-input::placeholder {
  text-align: left;
}

.sharing-links form .padding-bottom-1 {
  padding-bottom: 10px;
  display: block;
}

.sharing-links .row {
  border-bottom: 1px solid #004646;
}

.sharing-links .row .img {
  width: 60px;
  margin-bottom: 10px;
  height: 60px;
  border-radius: 7px;
  position: relative;
  float:left;
}

.info-sharing-url {
  float:left;
  margin-left: 20px;
  width: 130px;
}

.sharing-links .secondary-menu-body .box .row {
  height: 70px;
}

.caption-sharing-links {
  list-style: none;
  padding-left: 0;
  margin-left: 0;
  margin-bottom: 30px;
}

.caption-sharing-links li {
  float:left;
  width: 50%;
}

.caption-sharing-links li:last-child {
  text-align: right;
}
</style>

<script>
import {mapActions, mapState} from "vuex";
import axios from "axios";
import DatePickerComponent from '@buddy/views/widgets/DatePickerComponent.vue';
import TimePickerComponent from '@buddy/views/widgets/TimePickerComponent';



import Tooltip from 'hsy-vue-tooltip'
import Form from "../../../../events/views/widgets/bang/Form";
Vue.use(Tooltip)

export default {
  mixins: [
    require('@general/lib/mixin').default,
  ],
  data() {
    return {
      sharingLinks: [],
      sharingDomain: window.SHARING_DOMAIN,
    }
  },
  methods: {
    goToLink(url) {
      location.assign(url);
    },

    saveSettingSharingLink(event) {
      var form = event.target
      var formData = new FormData(form);
      axios.post('/api/saveSettingSharingLink', formData)
          .then((response) => {
            this.showSuccessNotification(this.trans('link_settings_saved'))

            $('body')[0].click()
          }, (response) => {
            // error callback
          });


    },
    onDatePickerChange($event) {
      $($event.target).removeClass('date-pristine');
    },

    async getSharingLinks(){
      await axios.get('/api/user/all-sharing-links').then(({data}) => {
        console.log('data');
        console.log(data.links);

        this.sharingLinks = data.links;
      })
          .catch(e => {
            this.mixin.methods.showErrorNotification(this.trans('can_not_get_sharing_links'))
          })
    },

    updateLink(id, event) {


      axios.post('/api/changeStatusLink', {
        id, status: event.target.value
      }).then(() => {

      });
    },

    ...mapActions([
      // ...
    ]),
  },

  filters: {
    formatHours(value) {
      var d = new Date(value);
      var hours = d.getUTCHours()
      var minutes = d.getUTCMinutes()

      if(hours < 10) {
        hours = '0' + hours
      }

      if(minutes < 10) {
        minutes = '0' + minutes
      }
      return  hours + ':' + minutes
    },

    formatDate(value) {
      if(value) {
        var d = new Date(value);
        var year = d.getUTCFullYear()
        var month = d.getUTCMonth()
        var day = d.getUTCDay()


        if(day < 10) {
          day = '0' + day
        }
        return  year + ':' + month + ':' + day
      }
      return '';
    }
  },

  computed: {
    ...mapState({
      user: 'profile',
      userIsPro: 'userIsPro',
    }),
  },
  components: {
    Form,
    DatePickerComponent,
    TimePickerComponent
  },

  mounted() {
    this.getSharingLinks();
  },
  activated() {
    this.getSharingLinks();

    app.$on('allSharingLinksDeleted', function(){
      this.getSharingLinks();
    });
  },
}


</script>

