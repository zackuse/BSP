import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util'
import MyTabbar from '@/page/components/Tabbar.vue'
import {
  Icon,
  NavBar,
  Toast,
  Grid,
  GridItem,
  Swipe,
  SwipeItem,
  Button
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
      Toast,
      Grid,
      GridItem,
      Swipe,
      SwipeItem,
      Button,
      MyTabbar
    }
  }
  data() {
    return {
      tabbarActive: 1,
      currentId: 'walk.walk',
      jwt: '',
      planList: [{
        icon: require('../../assets/images/ecology/icon_jiaoyisuo.png'),
        name: 'FCoin交易所'
      },{
        icon: require('../../assets/images/ecology/icon_BSPdaxue.png'),
        name: 'BSP大学'
      },{
        icon: require('../../assets/images/ecology/icon_BSPshangcheng.png'),
        name: 'BSP商城'
      },{
        icon: require('../../assets/images/ecology/icon_zhuliuhuobiwakuang.png'),
        name: '主流货币挖矿'
      },{
        icon: require('../../assets/images/ecology/icon_BSPquanceng.png'),
        name: 'BSP圈层'
      },{
        icon: require('../../assets/images/ecology/icon_bibijiedai.png'),
        name: '币币借贷'
      }],
      serviceList: [{
        icon: require('../../assets/images/ecology/icon_tongzhengmuxingsheji.png'),
        name: '通证模型设计'
      },{
        icon: require('../../assets/images/ecology/icon_jiaoyisuoshangshi.png'),
        name: '交易所上市'
      },{
        icon: require('../../assets/images/ecology/icon_meitifenfa.png'),
        name: '媒体分发'
      },{
        icon: require('../../assets/images/ecology/icon_chengshiluyan.png'),
        name: '城市路演'
      },{
        icon: require('../../assets/images/ecology/icon_shizhiguanli.png'),
        name: '市值管理'
      },{
        icon: require('../../assets/images/ecology/icon_shequnjianshe.png'),
        name: '社群建设'
      }]
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
    // await this.getList()
  }

  async onClickLeft() {
    this.popScene()
  }

  async getList() {
    var data = {
      jwt: this.jwt
    };
    var res = await get(process.env.BASE_API + '/server/hall/guangyiguang', data);

    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    this.walkList = res.data.data.data
  }

}
