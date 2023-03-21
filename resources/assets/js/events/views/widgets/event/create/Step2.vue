<template>
    <div class="b-box">
        <div class="w-form__text text-center">
            {{ trans('add_event_details') }}
        </div><!--w-form__text-->

        <form data-abide2 novalidate data-vv-scope="form2" @submit.prevent="goToStep3">
            <div class="row">
                <div class="small-12 columns">
                    <label>
                        <textarea class="gray-input" maxlength="510" name="description" v-bind:placeholder="trans('event_description')" v-bind:data-vv-as="trans('event_description')" required v-model="description" v-validate="'required'" :class="{'is-invalid-input': errors.has('form2.description')}" rows="4"></textarea>

                        <span class="form-error" :class="{'is-visible': errors.has('form2.description')}">
                          {{ errors.first('form2.description') }}
                        </span>
                    </label>
                </div>

                <div class="small-12 columns createEventLinkMyProfile">
                    <label class="b-checkbox">
                        <input type="checkbox" v-model="is_profile_linked" />
                        <svg class="icon icon-Checkbox--register"><use v-bind:xlink:href="symbolsSvgUrl('icon-Checkbox')"></use></svg>
                        <span>{{ trans('events.link_my_profile') }}</span>
                    </label><!--b-checkbox-->
                </div>

                <div class="small-12 columns w-form__text text-center margin-top-15">
                    {{ trans('add_tags_optional') }}
                </div><!--w-form__text-->

                <ul class="small-12 columns b-list__block">
                    <li>
                        <div class="w-tags" v-if="tags.length">
                            <span class="b-label main label" v-for="tag in tags">
                                #{{ tag }}
                                <svg class="icon icon-close" @click="deleteTag(tag)"><use v-bind:xlink:href="symbolsSvgUrl('icon-close')"></use></svg>
                            </span>
                        </div>

                        <a class="item-link item-content">
                            <input type="text" maxlength="75" v-model='newTag' class="gray-input gray-input--forms no-swiping w-tags__input" @keypress.enter.prevent="addTag" v-bind:placeholder="trans('add_tag')" />
                        </a>
                    </li>
                </ul><!--b-list__block-->
            </div>

            <div class="row align-middle margin-top-15">
                <fieldset class="text-center" :class="{'columns': true, 'small-12': isMobile, 'small-12': isDesktop}">
                    <button class="bb-button-green" type="submit" id="button2">
                        {{ trans('reg.next') }}
                    </button>
                </fieldset>
            </div>
        </form>
    </div>
</template>

<style scoped>
    .b-checkbox.desktop .text {
        text-transform: capitalize;
    }
    .gray-input--forms, .gray-input--forms:focus {
        font-size: 14px;
    }
    .gray-input::placeholder {
        font-size: 15px !important;
    }
    .w-tags .label {
        font-size: 16px;
    }
</style>

<script>
    export default {
        mixins: [require('@general/lib/mixin').default],
        props: ['vars'],
        data() {
            return this.vars;
        },
        methods: {
            goToStep1() {
                this.step = 1;
            },
            goToStep3() {
                let callback = () => {
                    return this.$validator.validateAll('form2').then((result) => {
                        if (result) {
                            this.scrollEventsPageTop(1);

                            this.step = 3
                        }
                    });
                };

                this.runLoadingFunction('#button2', callback);
            },
            addTag() {
                if (this.newTag && !this.tags.includes(this.newTag)) {
                    this.tags.push(this.newTag)
                }

                this.newTag = ''
            },
            deleteTag(tag) {
                const index = this.tags.findIndex(v => v == tag);

                this.tags.splice(index, 1);
            }
        }
    }
</script>
