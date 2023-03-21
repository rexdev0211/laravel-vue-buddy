<template>
    <div dusk="registration-step-1" class="step">
        <div class="headline" v-html="trans('reg.enter_email_address')"></div>
        <div class="registration">
            <form data-abide2 novalidate data-vv-scope="form1" @submit.prevent="goToStep2">
                <div class="form-inner">
                    <label>
                        <input class="form-control" type="email" v-bind:placeholder="trans('email')" v-bind:data-vv-as="trans('email')" required v-model="email" v-validate="'required|email'" :class="{'is-invalid-input': errors.has('form1.email')}" name="email">

                        <span class="form-error" :class="{'is-visible': errors.has('form1.email')}">
                          {{ errors.first('form1.email') }}
                        </span>
                    </label>

                    <label>
                        <input class="form-control" type="password" id="password" v-bind:placeholder="trans('password')" v-bind:data-vv-as="trans('password')" required v-model="password" v-validate="'required|min:6'" :class="{'is-invalid-input': errors.has('form1.password')}" name="password">

                        <span class="form-error" :class="{'is-visible': errors.has('form1.password')}">
                          {{ errors.first('form1.password') }}
                        </span>
                    </label>

                    <label>
                        <input
                            v-if="isTouch() && isIos()"
                            id="dob"
                            name="dob"
                            v-model="dob"
                            type="date"
                            @change="onDatePickerChange"
                            v-bind:placeholder="trans('reg.birthdate')"
                            v-bind:data-vv-as="trans('reg.birthdate')"
                            v-validate="{required: true, date_format: 'yyyy-MM-dd'}"
                            :class="{ 'form-control': true, 'date-pristine':true, 'datepicker-input-reg': true, 'is-invalid-input': errors.has('form1.dob') }"
                            required
                        />
                        <DatePickerComponent
                            v-else
                            id="dob"
                            name="dob"
                            v-model="dob"
                            v-bind:language="appLanguage"
                            v-bind:placeholder="trans('reg.birthdate')"
                            v-bind:data-vv-as="trans('reg.birthdate')"
                            v-bind:v-validate="{required: true, date_format: 'yyyy-MM-dd'}"
                            v-bind:input-class="{'form-control': true, 'datepicker-input-reg': true, 'is-invalid-input': errors.has('form1.dob')}"
                            v-bind:centered="isMobile"
                            required
                        ></DatePickerComponent>

                        <span class="form-error" :class="{'is-visible': errors.has('form1.dob')}">
                          {{ errors.first('form1.dob') }}
                        </span>
                    </label>
                </div>
                <button class="btn btn-registration-submit green" type="submit" id="button1">
                    {{ trans('reg.next') }}
                </button>
            </form>
        </div>
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
            onDatePickerChange($event) {
                $($event.target).removeClass('date-pristine');
            },
            async goToStep2() {
                this.showLoadingButton('#button1')
                let validated = await this.$validator.validateAll('form1')
                if (validated) {
                    const dataVars = {email: this.email, password: this.password, dob: this.dob}
                    try {
                        let response = await axios.post('/api/register/validateEmailPassDob', dataVars)
                        if (response.status === 200) {
                            this.step = 2
                        }
                    } catch (error) {}
                }
                this.restoreLoadingButton('#button1')
            },
        },
        mounted() {

        },
    }
</script>
