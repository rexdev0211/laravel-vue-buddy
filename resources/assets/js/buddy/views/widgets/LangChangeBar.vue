<template>
    <div class="w-lang">
        <button class="b-btn__lang" type="button" data-toggle="lang" style="margin:0;">
            <span v-if="appLanguage == 'en'">{{ getLangText('english') }}</span>
            <span v-if="appLanguage == 'de'">{{ getLangText('german') }}</span>
            <span v-if="appLanguage == 'fr'">{{ getLangText('french') }}</span>
            <span v-if="appLanguage == 'it'">{{ getLangText('italian') }}</span>
            <span v-if="appLanguage == 'nl'">{{ getLangText('dutch') }}</span>
            <span v-if="appLanguage == 'es'">{{ getLangText('spanish') }}</span>
            <span v-if="appLanguage == 'pt'">{{ getLangText('portuguese') }}</span>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
        </button>

        <ul class="dropdown-pane float-right" id="lang" data-dropdown data-close-on-click="true">
            <li><a v-if="appLanguage != 'en'" @click="setAppLanguage('en')">{{ getLangText('english') }}</a></li>
            <li><a v-if="appLanguage != 'de'" @click="setAppLanguage('de')">{{ getLangText('german') }}</a></li>
            <li><a v-if="appLanguage != 'fr'" @click="setAppLanguage('fr')">{{ getLangText('french') }}</a></li>
            <li><a v-if="appLanguage != 'it'" @click="setAppLanguage('it')">{{ getLangText('italian') }}</a></li>
            <li><a v-if="appLanguage != 'nl'" @click="setAppLanguage('nl')">{{ getLangText('dutch') }}</a></li>
            <li><a v-if="appLanguage != 'es'" @click="setAppLanguage('es')">{{ getLangText('spanish') }}</a></li>
            <li><a v-if="appLanguage != 'pt'" @click="setAppLanguage('pt')">{{ getLangText('portuguese') }}</a></li>
        </ul>
    </div><!--w-lang-->
</template>

<script>
    export default {
        mixins: [require('@general/lib/mixin').default],
        methods: {
            getLangText(lang) {
                if (this.isMobile) {
                    return this.trans(`${lang}_short`)
                } else {
                    return this.trans(`${lang}`)
                }
            }
        }
    }
</script>