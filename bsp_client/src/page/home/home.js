import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util';
import MyTabbar from '@/page/components/Tabbar.vue'
import {
  NavBar,
  Grid,
  GridItem,
  Toast,
  Icon,
  NoticeBar
} from 'vant'
require('@/framework/functions')

@Component({
  props: {

  }
})

export default class HomeScene extends QScene {
  mycomponent() {
    return {
      MyTabbar,
      NavBar,
      Grid,
      GridItem,
      Icon,
      NoticeBar
    }
  }
  data() {
    return {
      tabbarActive: 0,
      currentId: 'home.home',
      homeList: [{
        name: 'BSP 引擎计划',
        number: 10000,
        unit: '万'
      },{
        name: '已发放BSP生态奖励',
        number: 10000,
        unit: '(枚)'
      },{
        name: '昨日BSP生态奖励',
        number: 10000,
        unit: '(枚)'
      },{
        name: 'BSP生态平台总穿梭力',
        number: 10000,
        unit: '(s)'
      },{
        name: '个人穿梭力',
        number: 10000,
        unit: '(s)'
      },{
        name: 'BSP穿梭力奖励',
        number: 10000,
        unit: '(s)'
      }]
    }
  }

  async onVueCreated() {
    this.noticeStatus = this.getParams().showNotice
    this.jwt = Store.getStore().get('jwt')
  }

  async navigateTo(url) {
    this.pushScene(url)
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
