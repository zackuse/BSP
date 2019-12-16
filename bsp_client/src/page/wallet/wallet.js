import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util'
import MyTabbar from '@/page/components/Tabbar.vue'

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

export default class WalletScene extends QScene {
  mycomponent() {
    return {
      MyTabbar,
      Icon,
      NavBar,
      Cell,
      CellGroup,
      Toast
    }
  }
  data() {
    return {
      tabbarActive: 2,
      currentId: 'wallet.wallet',
      showMoney: true,
      usdt: 35345,
      cny: 24,
      list: [
        {
          img: require("../../assets/images/wallet/icon_chongzhi.png"),
          title: '充值',
          url: 'recharge.recharge'
        },
        {
          img: require("../../assets/images/wallet/icon_chongzhi.png"),
          title:'提现',
          url: 'withdraw.withdraw'
        },
        {
          img: require("../../assets/images/wallet/icon_chongzhi.png"),
          title: '记录',
          url: 'take.take'
        }
      ],
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
    // await this.getUser()
  }

  async toWallet(item) {
    this.pushScene(item.url)
  }

  async changeMoney() {
    this.showMoney = !this.showMoney
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
    this.totalshouyi = res.data.data.totalshouyi
    this.usdt = this.userInfo.usdt.toFixed(4)
  }

  async skipClick(url){
    this.pushScene(url)
  }

}
