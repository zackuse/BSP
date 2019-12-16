import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util'
import QRCode from "qrcode"
import {
  Icon,
  NavBar,
  Toast
} from 'vant'
require('@/framework/functions')

@Component({
  props: {

  },
})

export default class ShareScene extends QScene {
  mycomponent() {
    return {
      Icon,
      NavBar,
      Toast
    }
  }
  data() {
    return {
      jwt: '',
      qrcode: '',
      userInfo: {}
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
    await this.getShare()
    await this.getUser()
  }

  async onClickLeft() {
    this.popScene()
  }

  async getShare() {
    var data = {
      jwt: this.jwt
    };
    var res = await get(process.env.BASE_API + '/server/hall/tuiguang', data);
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    QRCode.toDataURL(res.data.data.data)
      .then(url => {
        this.qrcode = url;
      })
      .catch(err => {
        console.error(err)
      })
  }

  async getUser() {
    this.showLoading = true;
    var data = {
      jwt: this.jwt
    };
    var res = await get(process.env.BASE_API + '/server/login/loaduser', data);
    this.showLoading = false;
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    this.userInfo = res.data.data.user
  }

}
