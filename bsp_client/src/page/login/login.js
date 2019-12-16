import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
  Button,
  CellGroup,
  Field,
  Icon,
  Toast,
  Dialog
} from 'vant'
import { get} from '@/framework/request'
import { Store } from '@/framework/utils/util';
import Waiting from '@/page/components/Waiting.vue'
require('@/framework/functions')

@Component({
  props: {
    // methods: Object
  }
})
export default class LoginScene extends QScene {
  mycomponent() {
    return {
      Button,
      Field,
      CellGroup,
      Icon,
      Toast,
      Dialog,
      Waiting
    }
  }
  data() {
    return {
      phone: "",
      password: "",
      yzcode: "",
      showLoading: false,
      showReg: true
    }
  }

  async onVueCreated() {
    if (this.login = Store.getStore().get('login')) {
      this.login = Store.getStore().get('login')
      this.phone = this.login.phone
      this.password = this.login.password
    }
  }

  async toReg() {
    this.pushScene('login.register')
  }

  async toPwd() {
    this.pushScene('login.pwd')
  }

  async focus(){
    this.showReg = false
  }

  async blur(){
    this.showReg = true
  }

  //立即登录
  async toLogin() {
    this.pushScene('home.home', {
      showNotice: 1
    })
    // if (this.phone == "") {
    //   Toast("请输入手机号")
    //   return
    // }
    // if (this.password == "") {
    //   Toast("请输入密码")
    //   return
    // }
    // var data = {
    //   phone: this.phone,
    //   password: this.password,
    // }
    // this.showLoading = true
    // var res = await get(process.env.BASE_API + '/server/login/login', data)
    // this.showLoading = false
    // if (res.data.errcode != 0) {
    //   Toast(res.data.errmsg)
    //   return
    // }
    // var store = Store.getStore()
    // store.set('jwt', res.data.data.jwt)
    // store.set('login', {
    //   phone: this.phone,
    //   password: this.password
    // })

    // Toast.success("登录成功")
    // await this.wait(1000)
    // this.pushScene('home.home', {
    //   showNotice: 1
    // })
  }
}
