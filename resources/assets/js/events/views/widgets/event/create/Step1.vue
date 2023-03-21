<template>
    <div>
        <form data-abide2 novalidate data-vv-scope="form1" @submit.prevent="goToStep2">
            <div class="center">
                <input type="hidden" name="type" v-model="type" v-validate="{required: true}" />
                <div class="row">
                    <div class="small-12 form-error text-center" :class="{'is-visible': errors.has('form1.type')}">
                        {{ trans('events.type.required') }}
                    </div>
                    <div class="tabs event-category-tabs">
                        <div class="tab friends"
                            :class="{'active': type === 'friends', '': type !== 'friends'}"
                            @click="setType('friends')">
                            <span>{{ trans('events.type.friends') }}</span>
                        </div>
                        <div class="tab middle fun"
                            :class="{'active': type === 'fun', '': type !== 'fun'}"
                            @click="setType('fun')">
                            <span>{{ trans('events.type.fun') }}</span>
                        </div>
                        <div class="tab last bang"
                            :class="{'active': type === 'bang', '': type !== 'bang'}"
                            @click="setType('bang')">
                            <span>{{ trans('events.type.bang') }}</span>
                        </div>
                    </div>
                </div>
                <div class="candy-question" v-if="type === 'fun' || type === 'bang'">
                    <div class="checkbox-container">
                        <label class="checkbox-label" :title="trans('events.type.chemsfriendly')">
                            <input type="checkbox" v-model="chemsfriendly">
                            <span class="checkbox-custom"></span>
                            <div class="input-title"></div>
                        </label>
                    </div>
                </div>
                <div class="datepicker">
                    <div class="date today active"><span>today</span></div>
                    <div class="date tomorrow"><span>tomorrow</span></div>
                    <div class="date picker">
                        <label>
                            <input class="ios-date-input" required
                                    type="date"
                                    name="event_date"
                                      id="event_date"
                                    v-if="isTouch() && isIos()"
                      v-bind:placeholder="errors.has('form1.event_date') ? '' : trans('date')"
                       v-bind:data-vv-as="trans('date')"
                                 v-model="event_date"
                              v-bind:min="todayDate()"
                              v-bind:max="inAYearDate()"
                              v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: [todayDate(), inAYearDate(), true]}"
                                  :class="{'datepicker-input-reg': true, 'is-invalid-input': errors.has('form1.event_date')}" />
                            <DatePickerComponent name="event_date" required v-else
                                                   id="event_date"
                                      v-bind:language="appLanguage"
                                   v-bind:placeholder="trans('date')"
                                    v-bind:data-vv-as="trans('date')"
                                           v-bind:min="todayDate()"
                                           v-bind:max="inAYearDate()"
                                              v-model="event_date"
                                    v-bind:v-validate="{required: true, date_format: 'yyyy-MM-dd', date_between: [todayDate(), inAYearDate(), true]}"

                                   v-bind:input-class="{'datepicker-input-reg': true, 'is-invalid-input': errors.has('form1.event_date')}"></DatePickerComponent>

                            <span class="form-error" :class="{'is-visible': errors.has('form1.event_date')}">
                              {{ errors.first('form1.event_date') }}
                            </span>
                        </label>
                    </div>
                </div>
                <div class="timepicker">
                    <span class="time"></span>
                    <label>
                        <input class="form-control" type="text" v-bind:placeholder="trans('time')" maxlength="50" v-bind:data-vv-as="trans('time')" required v-model="time" v-validate="{required: true}" :class="{'is-invalid-input': errors.has('form1.time')}" name="time">

                        <span class="form-error" :class="{'is-visible': errors.has('form1.time')}">
                          {{ errors.first('form1.time') }}
                        </span>
                    </label>
                </div>
                <div class="name-edit">
                    <label>
                        <input type="text" v-bind:placeholder="trans('title')" maxlength="30" v-bind:data-vv-as="trans('title')" required v-model="title" v-validate="{required: true}" :class="{'is-invalid-input': errors.has('form1.title')}" name="title">

                        <span class="form-error" :class="{'is-visible': errors.has('form1.title')}">
                          {{ errors.first('form1.title') }}
                        </span>
                    </label>
                </div>
                <div class="box add-margin">
                    <div id="event-main-pic" class="profile-main-pic">
                        <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                        <div class="edit"></div>
                    </div>
                    <div class="media-catalog" v-if="type === 'friends' || type === 'fun'">
                        <div class="row">
                            <div class="item pic free-plan added">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                            <div class="item pic">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                            <div class="item pic">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                            <div class="item pic">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                            <div class="item pic">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="item free-plan vid">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                            <div class="item vid">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                            <div class="item vid">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                            <div class="item vid">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                            <div class="item vid">
                                <div class="img" style="background-image: url(dist/img/discover/face.jpg);"></div>
                                <div class="close"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box description-link" v-if="type === 'friends' || type === 'fun'">
                    <div class="row">
                        <div class="headline">Description</div>
                        <textarea class="description" rows="1" placeholder="What do you want to do?"></textarea>
                    </div>
                    <div class="row">
                        <div class="title">Link my profile</div>
                        <div class="field">
                            <div class="toggle-switch">
                              <input type="checkbox" id="link-my-profile" name="push-notifications" checked>
                              <label for="link-my-profile">
                                <span class="toggle-track"></span>
                              </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="row">
                        <div class="title">Location</div>
                        <div class="options">
                            <input id="gps" type="radio" class="toggle gps" name="friends-event-location" checked>
                            <label for="gps" class="gps"><span>GPS</span></label>
                            <input id="map" type="radio" class="toggle map" name="friends-event-location">
                            <label for="map" class="map"><span>Map</span></label>
                        </div>
                        <div class="detail location">{display full address here}</div>
                    </div>
                </div>
            </div>
            <div class="address">
                <div class="input-wrapper">
                    <input type="text" name="event-address" placeholder="Address">
                </div>
                <div class="map-box" style="background-image: url(dist/img/discover/map.jpg);"></div>
                <div class="float-button">
                    <button class="btn set-location">Set location</button>
                </div>
            </div>

            <button class="btn publish" type="submit" id="button1">
                <!-- Publish -->
                {{ trans('reg.next') }}
            </button>
        </form>
    </div>
</template>

<script>
    import DatePickerComponent from '@buddy/views/widgets/DatePickerComponent.vue';

    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['vars'],
        data() {
            return this.vars;
        },
        components: {
            DatePickerComponent,
        },
        methods: {
            setType(type) {
                this.type = type
                if (type !== 'fun'){
                    this.chemsfriendly = false
                }
            },
            goToStep2() {
                let callback = () => {
                    return this.$validator.validateAll('form1')
                        .then((result) => {
                            if (result) {
                                this.scrollEventsPageTop(1);

                                this.step = 2
                            }
                        });
                };

                this.runLoadingFunction('#button1', callback);
            },
        },
    }
</script>
