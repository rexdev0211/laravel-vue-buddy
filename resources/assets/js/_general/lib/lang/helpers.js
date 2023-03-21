import {fallbackLocale, default as messages} from './messages';

export function getUserLanguage() {
    //read it from local storage
    let lang = localStorage.getItem('lang');

    //get it from browser
    if(!lang) {
        const firstLocale = navigator.languages && navigator.languages[0] || navigator.language || navigator.userLanguage;
        const localeLang = firstLocale.split('-')[0];
        if (messages[localeLang]) {
            lang = localeLang;
        }
    }

    //if lang not exists -> fall back to default
    if (!messages[lang]) {
        lang = fallbackLocale;
    }

    //save so next time we don't have to calculate it again
    localStorage.setItem('lang', lang);

    return lang;
}

export function setUserLanguage(lang) {
    app.$i18n.locale = lang;
    localStorage.setItem('lang', lang);
}