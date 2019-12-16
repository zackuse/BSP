import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util';

import {
  Icon,
  NavBar,
  Cell,
  CellGroup,
  Toast
} from 'vant'
require('@/framework/functions')

@Component({
  props: {

  },
})

export default class AboutScene extends QScene {
  mycomponent() {
    return {
      Icon,
      NavBar,
      Cell,
      CellGroup,
      Toast
    }
  }
  data() {
    return {
      jwt: '',
      detail: {},
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
    this.id = this.getParams().item.id
    this.getDetail()
  }

  async getDetail() {
    var data = {
      jwt: this.jwt,
      id: this.id
    };
    var res = await get(process.env.BASE_API + '/server/hall/gethelpdoc', data);
    this.showLoading = false;
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
  }
  async onClickLeft() {
    this.popScene()
  }
}
