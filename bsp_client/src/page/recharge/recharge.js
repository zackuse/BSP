import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import { get } from '@/framework/request'
import {
  Store,
  ClipBoard
} from '@/framework/utils/util';
import QRCode from "qrcode";
import {
  Icon,
  NavBar,
  Toast,
  Collapse, 
  CollapseItem
} from 'vant'
require('@/framework/functions')

@Component({
  props: {

  },
})

export default class RechargeScene extends QScene { 
  mycomponent() {
    return {
      Icon,
      NavBar,
      Toast,
      Collapse, 
      CollapseItem
    }
  }
  data() {
    return {
      qrcode: "",
      userInfo: {},
      jwt: '',
      activeNames:['']
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
    await this.getUser()
  }

  async onClickLeft() {
    this.popScene()
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
    QRCode.toDataURL(this.userInfo.address)
      .then(url => {
        this.qrcode = url;
      })
      .catch(err => {
        console.error(err)
      })
  }

  async copy() {
    if (ClipBoard.set(this.address)) {
      Toast.success("复制成功")
    } else {
      Toast.fail("复制失败")
    }
  }

}
