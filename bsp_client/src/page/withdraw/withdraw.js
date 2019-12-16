import {
  QScene
} from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
  get,
  post
} from '@/framework/request'

import {
  Store,
  ClipBoard
} from '@/framework/utils/util';

import {
  Icon,
  NavBar,
  Toast,
  CellGroup,
  Field,
  Button
} from 'vant'
require('@/framework/functions')
 
@Component({
  props: {

  },
})

export default class WithdrawScene extends QScene {
  mycomponent() {
    return {
      Icon,
      NavBar,
      Toast,
      CellGroup,
      Field,
      Button
    }
  }
  data() {
    return {
      num: '',
      address: '',
      tradePwd: '',
      jwt: '',
      goole:'',
      sms:''
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
  }

  async onClickLeft() {
    this.popScene()
  }

  async confirm() {
    if (this.num == '') {
      Toast('请输入提币数量')
      return
    }
    if (this.num < 100) {
      Toast('提币数量不能小于100')
      return
    }
    if (this.address == '') {
      Toast('请输入钱包地址')
      return
    }
    if (this.tradePwd == '') {
      Toast('请输入交易密码')
      return
    }
    this.showLoading = true;
    var data = {
      jwt: this.jwt,
      count: this.num,
      address: this.address,
      jiaoyipassword: this.tradePwd
    };
    var res = await get(process.env.BASE_API + '/server/hall/tixian', data);
    this.showLoading = false;
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    Toast.success('提币成功')
  }
}
