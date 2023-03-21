<template>
    <div>
        <div class="box">
            <div class="headline">{{ trans('account') }}</div>
            <div class="row">
                <div class="title">{{ trans('display_name') }}</div>
                <div class="field notranslate">
                    <input
                        v-model="user.name"
                        type="text"
                        name="name"
                        class="form-control no-swiping"
                        v-bind:data-vv-as="trans('display_name')"
                        v-validate="{required: true, min: 3, max: 32}"
                        @focus="rememberProfileOldValue"
                        @change="saveProfileChange"
                    >
                </div>
            </div>
            <span class="form-error" :class="{'is-visible': errors.has('name')}">
              {{ errors.first('name') }}
            </span>

            <div class="row">
                <div class="title">{{ trans('reg.birthdate') }}</div>
                <div class="field">
                    <input
                        v-if="isTouch() && isIos()" type="date"
                        v-model="user.dob"
                        class="form-control no-swiping datepicker-input-profile"
                        name="dob"
                        id="dob"
                        v-bind:max="maxAdultDate()"
                        v-bind:data-vv-as="trans('reg.birthdate')"
                        required
                        v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: ['1900-01-01', maxAdultDate(), true]}"
                        @change="saveProfileChange"
                    >
                    <DatePickerComponent
                        v-else
                        v-model="user.dob"
                        name="dob"
                        id="dob"
                        input-class="form-control no-swiping datepicker-input-profile"
                        v-bind:language="appLanguage"
                        v-bind:max="maxAdultDate()"
                        v-bind:data-vv-as="trans('reg.birthdate')"
                        v-bind:to-right="isMobile"
                        v-bind:v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: ['1900-01-01', maxAdultDate(), true]}"
                        required
                        @change="saveProfileChange"
                    ></DatePickerComponent>
                </div>
            </div>
            <span class="form-error" :class="{'is-visible': errors.has('dob')}">
              {{ errors.first('dob') }}
            </span>

            <div class="row">
                <div class="title">{{ trans('email') }}</div>
                <div class="field">
                    <input type="email" name="email" v-model="user.email"
                        v-bind:data-vv-as="trans('email')"
                        v-validate="'required|email'" class="form-control no-swiping"
                        @focus="rememberProfileOldValue" @change="saveProfileChange">
                </div>
            </div>
            <span class="form-error" :class="{'is-visible': errors.has('email')}">
              {{ errors.first('email') }}
            </span>

            <div class="row">
                <div class="title">{{ trans('password') }}</div>
                <div class="field">
                    <div type="password" class="form-control no-swiping" @click="showPasswordForm">{{ trans('change_password') }}</div>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="headline">{{ trans('security') }}</div>
            <div class="row">
                <div class="title">{{ trans('display_sensitive_events') }}</div>
              <div class="field">
                <div class="toggle-switch">
                  <input type="checkbox" id="app-display-sensitive-events"
                         v-model="user.app_view_sensitive_events"
                         name="app_view_sensitive_events"
                         true-value="yes"
                         false-value="no"
                         @change="userUpdate">
                  <label for="app-display-sensitive-events">
                    <span class="toggle-track"></span>
                  </label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="title">{{ trans('display_sensitive_media') }}</div>
              <div class="field" v-if="userIsPro">
                <div class="toggle-switch">
                  <input type="checkbox" id="app-display-sensitive-media"
                         v-model="user.app_view_sensitive_media"
                         true-value="yes"
                         false-value="no"
                         name="app_view_sensitive_media"
                         @change="userUpdate">
                  <label for="app-display-sensitive-media">
                    <span class="toggle-track"></span>
                  </label>
                </div>
              </div>
              <div class="field" v-else>
                <a class="go-pro" @click="goTo('/profile/pro')"></a>
              </div>
            </div>
          <div class="row sensitive">
            <div class="title">{{ trans('display_sensitive_media_web') }}</div>
            <div class="field">
              <div class="toggle-switch">
                <input type="checkbox" id="web-display-sensitive-content"
                       v-model="user.web_view_sensitive_content"
                       true-value="yes"
                       false-value="no"
                       name="web_view_sensitive_content"
                       @change="userUpdate">
                <label for="web-display-sensitive-content">
                  <span class="toggle-track"></span>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="box">
            <div class="headline">{{ trans('discreet_mode') }}</div>
            <div class="row" id="buy-pro">
                <div class="title">{{ trans('hide_profile_completely') }}</div>
                <div class="field" v-if="userIsPro">
                    <div class="toggle-switch">
                        <input type="checkbox" id="invisible"
                               v-model="user.invisible"
                               true-value="yes"
                               false-value="no"
                               name="invisible"
                               @change="userUpdate">
                        <label for="invisible">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>
                <div class="field" v-else>
                    <a class="go-pro" @click="goTo('/profile/pro')"></a>
                </div>
                <div class="tip">{{ trans('hide_profile_completely_tip') }}  
              </div>
            </div>
        </div>
        <div class="box">
            <div class="headline" v-if="!isApp || user.push_notifications">{{ trans('notifications') }}</div>
            <div class="row" v-if="isApp && user.push_notifications">
                <div class="title">{{ trans('push_notifications') }}</div>
                <div class="field">
                    <div class="toggle-switch">
                        <input type="checkbox" id="push-notifications"
                            v-model="user.push_notifications"
                            name="push_notifications"
                            @change="userUpdate">
                        <label for="push-notifications">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('notification_sound') }}</div>
                <div class="field">
                    <div class="toggle-switch">
                        <input type="checkbox" id="notification-sound"
                            v-model="user.notification_sound"
                            name="notification_sound"
                            @change="userUpdate">
                        <label for="notification-sound">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('subscribe_to_newsletter') }}</div>
                <div class="field">
                    <div class="toggle-switch">
                        <input type="checkbox" id="newsletter"
                            v-model="user.subscribed"
                            name="subscribed"
                            @change="userUpdate">
                        <label for="newsletter">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('emails_reminders') }}</div>
                <div class="field">
                    <select id="email-summary" class="form-control no-swiping"
                        v-model="user.email_reminders"
                        name="email_reminders"
                        @change="saveProfileChange">
                        <option value="daily">{{ trans('daily') }}</option>
                        <option value="weekly">{{ trans('weekly') }}</option>
                        <option value="monthly">{{ trans('monthly') }}</option>
                        <option value="never">{{ trans('never') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="headline">{{ trans('preferences') }}</div>
            <div class="row">
                <div class="title">{{ trans('show_age') }}</div>
                <div class="field">
                    <div class="toggle-switch">
                        <input type="checkbox" id="show-age"
                            v-model='user.show_age'
                               true-value="yes"
                               false-value="no"
                            name="show_age"
                            @change="userUpdate">
                        <label for="show-age">
                            <span class="toggle-track"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="title">{{ trans('language') }}</div>
                <div class="field">
                    <select id="language" class="form-control no-swiping" @change="changeLanguage" name="language">
                        <option :selected="appLanguage == 'en'" value="en">{{ trans('english') }}</option>
                        <option :selected="appLanguage == 'de'" value="de">{{ trans('german') }}</option>
                        <option :selected="appLanguage == 'fr'" value="fr">{{ trans('french') }}</option>
                        <option :selected="appLanguage == 'it'" value="it">{{ trans('italian') }}</option>
                        <option :selected="appLanguage == 'nl'" value="nl">{{ trans('dutch') }}</option>
                        <option :selected="appLanguage == 'es'" value="es">{{ trans('spanish') }}</option>
                        <option :selected="appLanguage == 'pt'" value="pt">{{ trans('portuguese') }}</option>
                    </select>
                </div>
            </div>
          <div class="row">
            <div class="title">{{ trans('unit_system') }}</div>
            <div class="field">
              <select id="unit-system" class="form-control no-swiping"
                      v-model="user.unit_system"
                      name="unit_system"
                      @change="saveProfileChange">
                <option value="metric">{{ trans('metric') }}</option>
                <option value="imperial">{{ trans('imperial') }}</option>
              </select>
            </div>
          </div>
          <div class="row" id="customize-sharing-links">
            <div class="title pointer"
                 @click="goTo('/profile/customize-sharing-links')"
                 v-if="isMobile">{{ trans('customize_sharing_links') }}</div>
            <div class="title" @click="openCustomizeSharingLinks" v-else="!isMobile">{{ trans('customize_sharing_links') }}</div>
          </div>
        </div>
        <div class="box">
            <div class="headline">{{ trans('reset') }}</div>
            <div class="row" id="unblock-all-users">
                <div class="title" @click="showBlockedUsers">{{ trans('unblock_users') }}</div>
            </div>
          <div class="row" id="deactivate-or-delete-profile">
            <div class="title" @click="goTo('/profile/deactivate')" v-if="isMobile">{{ trans('deactivate_or_delete_my_profile') }}</div>
            <div class="title" @click="openProfileDeactivation" v-else="!isMobile">{{ trans('deactivate_or_delete_my_profile') }}</div>
          </div>
          <div class="row" id="delete-all-sharing-links">
            <div class="title" @click="goTo('/profile/delete_all_sharing_links')" v-if="isMobile">{{ trans('delete_all_sharing_links') }}</div>
            <div class="title" @click="openDeleteAllSharingLinks" v-else="!isMobile">{{ trans('delete_all_sharing_links') }}</div>
          </div>
            <div class="row">
                <div id="logout" class="field" @click="logout">
                    <span class="button logout"></span>
                    <span class="label">{{ trans('logout') }}</span>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="headline">Like and share here</div>
            <div class="row">
                <ul class="socials">
                    <li class="instagram">
                        <a href="https://instagram.com/buddy_net" rel="noopener noreferrer"></a>
                    </li>
                    <li class="facebook">
                        <a href="https://facebook.com/buddydate" rel="noopener noreferrer"></a>
                    </li>
                    <li class="twitter">
                        <a href="https://twitter.com/buddy_net" rel="noopener noreferrer"></a>
                    </li>
                    <li class="tiktok">
                        <a href="https://www.tiktok.com/@buddy.net" rel="noopener noreferrer"></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapState, mapActions} from 'vuex';
    import DatePickerComponent from '@buddy/views/widgets/DatePickerComponent.vue';
    import Map from '@buddy/views/widgets/Map.vue';

    export default {
      mixins: [require('@general/lib/mixin').default],
      components: {
        DatePickerComponent,
        Map,
      },
      computed: {
        ...mapState({
          user: 'profile',
          userIsPro: 'userIsPro',
        })
      },
      methods: {
        ...mapActions([
          'requirementsAlertShow',
          'openProfileDeactivation',
          'openDeleteAllSharingLinks',
          'openCustomizeSharingLinks',
        ]),
        userUpdate(event) {
          event.target.value = !this.user[event.target.name] || this.user[event.target.name] == 'no' ? 0 : 1;
          this.saveProfileChange(event, true);
        },
        showPasswordForm() {
          app.$emit('show-password-form');
        },
        showBlockedUsers() {
          if (app.isDesktop) {
            app.$emit('show-blocked-users');
          } else {
            this.goTo('/profile/unblock-users');
          }
        },
        changeLanguage(event) {
          app.setLanguage(event.target.value)
          this.saveProfileChange(event)
        },
        unblockUsers() {
          app.showLightLoading(true);

          axios.post('/api/unblockUsers').then(() => {
            app.showLightLoading(false);
            app.$store.commit('setBlockedCount', 0)
            this.showSuccessNotification('all_users_unblocked');
          });
        }
      },
    }
</script>
