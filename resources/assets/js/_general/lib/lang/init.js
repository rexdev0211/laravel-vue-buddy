import {fallbackLocale, default as messages} from './messages';
import {getUserLanguage} from './helpers';

const locale = getUserLanguage();

const i18n = new VueI18n({
    locale, // set locale
    fallbackLocale,
    messages, // set locale messages
});

export default i18n;