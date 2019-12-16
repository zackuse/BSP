import {
  QScene
} from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
  get,
  post
} from '@/framework/request'
import {
  strict
} from 'assert'

import {
  Popup,
  Loading
} from 'vant'

require('@/framework/functions')
var tenjin = require('tenjin')

@Component({
  props: {
    sonActive: Number,
    required: true,
    currentId: String
  },
  name:'Waiting'
})
export default class Waiting extends QScene {
  mycomponent() {
    return {
      Popup,
      Loading
    }
  }
  data() {
    return {
      show: true,
      closeOverlay: false
    }
  }

  async onVueCreated() {}

}
