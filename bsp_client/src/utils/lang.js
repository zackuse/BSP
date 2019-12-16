import Vue from 'vue'
import { dbstorage } from '@/utils/dbstorage'
import VueI18n from 'vue-i18n'
Vue.use(VueI18n)

dbstorage.init('language', ['lang', 'language'])

const lang = dbstorage.query('language', { language: 'language', lang: 'en' })
if (!lang || lang.length <= 0) {
  dbstorage.updateorinsert('language', {
    language: 'language',
  }, {
    language: 'language',
    lang: 'zh',
  })
}
const locale = lang.length <= 0 ? 'zh' : 'en'

const i18n = new VueI18n({
  locale: locale, // set locale
  messages: {
    'zh': require('@/assets/lang/zh'),
    'en': require('@/assets/lang/en'),
  },

})

export default {
  i18n,
}

