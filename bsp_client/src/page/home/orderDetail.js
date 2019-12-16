import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import { get } from '@/framework/request'
import { PAY_TYPE } from '@/common/globalConst';
import { Store } from '@/framework/utils/util';
import {
  Icon,
  NavBar,
  CellGroup,
  Field,
  Button,
  Dialog,
  Toast,
  Cell
} from 'vant'
require('@/framework/functions')

@Component({
  props: {

  },
  filters: {
    formatDate: function (value) {
      let date = new Date(value * 1000);
      let y = date.getFullYear();
      let MM = date.getMonth() + 1;
      MM = MM < 10 ? ('0' + MM) : MM;
      let d = date.getDate();
      d = d < 10 ? ('0' + d) : d;
      let h = date.getHours();
      h = h < 10 ? ('0' + h) : h;
      let m = date.getMinutes();
      m = m < 10 ? ('0' + m) : m;
      let s = date.getSeconds();
      s = s < 10 ? ('0' + s) : s;
      return y + '-' + MM + '-' + d + ' ' + h + ':' + m + ':' + s;
    },
    formatType: function (value) {
      return PAY_TYPE[value]
    }
  }
})

export default class DetailScene extends QScene {
  mycomponent() {
    return {
      Icon,
      NavBar,
      CellGroup,
      Field,
      Button,
      Dialog,
      Toast,
      Cell
    }
  }
  data() {
    return {
      orderNum: "100STP",
      nickname: "哈哈哈",
      time: "2019-101-10",
      address: "7489537rut",
      type: "微信",
      account: "lllallla",
      status: "已接单",
      showAgree: false,
      tradePwd: '',
      jwt: '',
      orderInfo: {},
      order: {},
      yongjin: 0,
      orderStp: 0,
      orderUsdt: 0
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
    this.orderInfo = this.getParams().item
    this.getDetail()
  }

  async onClickLeft() {
    this.popScene()
  }

  async getDetail() {
    var data = {
      jwt: this.jwt,
      orderid: this.orderInfo.orderid
    };
    var res = await get(process.env.BASE_API + '/api/pay/orderDetail', data);
    this.showLoading = false;
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    this.order = res.data.data.data
    this.orderNum = parseFloat(this.order.paycny).toFixed(2) + 'CNY ≈ ' + parseFloat(this.order.usdt).toFixed(4) + 'USDT'
    this.yongjin = Math.floor(this.order.paycny * this.order.yongjin) / 100
    this.orderStp = this.order.paycny
    this.orderUsdt = this.order.usdt
  }

  //同意
  async agree(item) {
    this.showAgree = true
    this.orderid = item.orderid
  }
  //确认同意
  async confirmAgree() {
    if (this.tradePwd == '') {
      Toast('请输入交易密码')
      return
    }
    var data = {
      jwt: this.jwt,
      orderid: this.orderInfo.orderid,
      jypassword: this.tradePwd
    }
    var res = await get(process.env.BASE_API + '/api/pay/orderConfirm', data);
    this.showLoading = false;
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    this.showAgree = false
    Toast.success("确认放行成功")
    this.getDetail()
  }
  //无效订单
  async refuse(item) {
    Dialog.confirm({
      title: '无效订单',
      message: '将此订单标记为无效订单？'
    }).then(() => {
      // on confirm
      this.confirmRefuse()
    }).catch(() => {
      // on cancel
    });
  }
  //确认拒绝
  async confirmRefuse() {
    var data = {
      jwt: this.jwt,
      orderid: this.orderInfo.orderid
    }
    var res = await get(process.env.BASE_API + '/api/pay/orderJujue', data);
    this.showLoading = false;
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    Toast.success("操作成功")
    this.getDetail()
  }
  //异常订单
  async abnormal() {
    Dialog.confirm({
      title: '异常订单',
      message: '将此订单标记为异常订单？'
    }).then(() => {
      this.abnormalOrder()
    }).catch(() => {
      
    });
  }

  async abnormalOrder(){
    var data = {
      jwt: this.jwt,
      orderid: this.orderInfo.orderid
    }
    var res = await get(process.env.BASE_API + '/api/pay/orderException', data);
    this.showLoading = false;
    if (res.data.errcode != 0) {
      Toast(res.data.errmsg)
      return
    }
    Toast.success("操作成功")
  }

}
