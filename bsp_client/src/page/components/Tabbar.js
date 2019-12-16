import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
  Tabbar,
  TabbarItem
} from 'vant'
require('@/framework/functions')

@Component({
  props: {
    sonActive: Number,
    required: true,
    currentId: String
  },
  name: 'MyTabbar'
})
export default class MyTabbar extends QScene {
  mycomponent() {
    return {
      Tabbar,
      TabbarItem
    }
  }
  data() {
    return {
      active: 0,
      tabList: [{
        name: "首页",
        wid: 'home.home',
        icon: 'sy'
      }, {
        name: "生态",
        wid: 'ecology.ecology',
        icon: 'st'
      }, {
        name: "资产",
        wid: 'wallet.wallet',
        icon: 'zs'
      }, {
        name: "我的",
        wid: 'mine.mine',
        icon: 'wd'
      }]
    }
  }

  async onVueCreated() { }

  async tabTo(item) {
    if (item.wid == this.currentId) {
      return
    }
    this.replaceScene(item.wid, {}, 'none')
  }

}
