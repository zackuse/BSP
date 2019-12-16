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

export default class SelectScene extends QScene {
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
      jwt: '',
      list:[
        {
          img:require('../../assets/images/ecology/num-1.png'),
          userName:'我是一个用户名',
          BSP:'7500.200',
          judge:0
        },
        {
          img:require('../../assets/images/ecology/num-2.png'),
          userName:'我是二个用户名',
          BSP:'7500.200',
          judge:0
        },
        {
          img:require('../../assets/images/ecology/num-3.png'),
          userName:'我是三个用户名',
          BSP:'7500.200',
          judge:0
        },
        {
          num:4,
          userName:'我是四个用户名',
          BSP:'7500.200',
        },
        {
          num:5,
          userName:'我是五个用户名',
          BSP:'7500.200',
        },
        {
          num:6,
          userName:'我是六个用户名',
          BSP:'7500.200',
        }
      ],
      
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
    // await this.getList()
    // var num=4;
    // if(this.list.img==''){
    //   this.list.img=num++
    // }
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
