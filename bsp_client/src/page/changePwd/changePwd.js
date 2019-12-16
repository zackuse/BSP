import {
  QScene
} from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
  get,
  post
} from '@/framework/request'

import {
  Store
} from '@/framework/utils/util';

import {
  Icon,
  NavBar,
  Cell,
  CellGroup
} from 'vant'
require('@/framework/functions')

@Component({
  props: {

  },
})

export default class WalletScene extends QScene {
  mycomponent() {
    return {
      Icon,
      NavBar,
      Cell,
      CellGroup
    }
  }
  data() {
    return {
      
    }
  }

  async onVueCreated() {
    this.user = Store.getStore().get('user')
  }

  
  async onClickLeft() {
    this.popScene()
  }

  async changeBD(){
    this.pushScene('changePwd.changeBD')
  }

}
