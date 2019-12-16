import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
  Button,
  CellGroup,
  Field,
  Icon,
  NavBar,
  Toast,
  Popup,
  Area
} from 'vant'
import { get } from '@/framework/request'
import regex from '@/common/regex'
import AREALIST from '@/common/area'
require('@/framework/functions')
var md5 = require('md5');

@Component({
  props: {
    // methods: Object
  }
})
export default class RegisterScene extends QScene {
  mycomponent() {
    return {
      Button,
      Field,
      CellGroup,
      Icon,
      NavBar,
      Toast,
      Popup,
      Area
    }
  }
  data() {
    return {
      phone: "",
      nick: "",
      smsCode: "",
      logPwd: "",
      conLogPwd: "",
      tradePwd: "",
      conTradePwd: "",
      tjPhone: "",
      city: "",
      isGet: true,
      time: 60,
      city: '',
      showCity: false,
      areaList: AREALIST
    }
  }

  async onVueCreated() { }

  async onClickLeft() {
    this.popScene()
  }

  async send(time) {
    if (this.phone == '') {
      Toast("请输入手机号码")
      return
    }
    var data = {
      phone: this.phone,
      nihaoma: md5(this.phone + 'nihaoma')
    }
    var res = await get(process.env.BASE_API + '/server/login/sms_cr', data)
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    Toast("发送成功")
    this.countDown(time)
  }

  async countDown(time) {
    var self = this
    self.isGet = false;
    var t = setInterval(function () {
      time--;
      self.time = time;
      if (time < 0) {
        clearInterval(t);
        time = 60;
        self.time = 60
        self.isGet = true;
      }
    }, 1000);
  }

  async toReg() {
    if (this.phone == "") {
      Toast("请输入手机号")
      return
    }
    if (!regex.phone.test(this.phone)) {
      Toast('手机号码格式错误');
      return false;
    }
    if (this.smsCode == "") {
      Toast("请输入短信验证码")
      return false;
    }

    if (this.logPwd == "") {
      Toast("请输入登录密码")
      return false;
    }
    if (this.conLogPwd == "") {
      Toast("请再次输入登录密码")
      return false;
    }
    if (this.conLogPwd != this.logPwd) {
      Toast("两次输入的登录密码不一致")
      return false;
    }

    if (this.tradePwd == "") {
      Toast("请输入交易密码")
      return false;
    }
    if (this.conTradePwd == "") {
      Toast("请再次输入交易密码")
      return false;
    }
    if (this.tradePwd != this.conTradePwd) {
      Toast("两次输入的交易密码不一致")
      return false;
    }
    if (this.tjPhone == "") {
      Toast("请输入推荐人ID")
      return false;
    }

    if (this.city == "") {
      Toast("请选择城市")
      return false;
    }

    var data = {
      phone: this.phone,
      password: this.logPwd,
      pay_password: this.tradePwd,
      code: this.smsCode,
      yaoqingma: this.tjPhone,
      city: this.city
    }
    var res = await get(process.env.BASE_API + '/server/login/register', data)

    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    Toast.success("注册成功")
    await this.wait(1000)
    this.popScene()
  }

  async confirm(arr){
    console.log(arr)
    this.showCity = false
    this.city = arr[0].name + arr[1].name + arr[2].name
  }
}
