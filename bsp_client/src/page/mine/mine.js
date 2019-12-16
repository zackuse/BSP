import { QScene } from '@/framework/QScene.js'
import Vue from 'vue'
import Component from 'vue-class-component'
import { get, post } from '@/framework/request'
import { Store } from '@/framework/utils/util';
import MyTabbar from '../components/Tabbar.vue'
import Waiting from '@/page/components/Waiting.vue'
import {
  Icon,
  Toast,
  NavBar,
  Dialog,
  CellGroup,
  Field,
  Cell,
  Popup,
  Uploader
} from 'vant'
Vue.use(Dialog);
require('@/framework/functions')

@Component({
  props: {
    // methods: Object
  }
})
export default class MineScene extends QScene {
  mycomponent() {
    return {
      Waiting,
      Icon,
      Toast,
      NavBar,
      MyTabbar,
      CellGroup,
      Field,
      Cell,
      Popup,
      Uploader
    }
  }

  data() {
    return {
      tabbarActive: 3,
      currentId: 'mine.mine',
      jwt: "",
      userList: [ {
        title: '个人资料',
        img: require("../../assets/images/mine/icon_gerenziliao .png"),
        url:'indiv.indiv',
        value:'',
        bool:true
      },
      {
        title: '身份认证',
        img: require("../../assets/images//mine/icon_shenfenrenzheng.png"),
        url: 'status.status',
        value:'未认证',
        bool:true
      },
      {
        title: '安全中心',
        img: require("../../assets/images/mine/icon_anquanzhongxin.png"),
        url: 'changePwd.changePwd',
        value:'',
        bool:true
      },
      {
        title: '邀请好友',
        img: require("../../assets/images/mine/icon_yaoqinghaoyou.png"),
        url: 'share.share',
        value:'', 
        bool:true
      },
      {
        title: '帮助中心',
        img: require("../../assets/images//mine/icon_bangzhuzhongxin.png"),
        url: 'help.help',
        value:'',
        bool:true
      },
      {
        title: 'BSP',
        img: require("../../assets/images/mine/icon_BSP.png"),
        url:'',
        value:'V.1.0',
        bool:false
      },
      {
        title: '意见反馈',
        img: require("../../assets/images/mine/icon_yijianfankui.png"),
        url: 'feedback.feedback',
        value:'' ,
        bool:true
      },
      {
        title: '关于我们',
        img: require("../../assets/images//mine/icon_guanyuwomen.png"),
        url:'about.about',
        value:'',
        bool:true
      }],
      showLoading: false,
      userInfo: {},
      showAgent: false,
      reason: '',
      showService: false,
      serviceInfo: {},
      showNick: false,
    }
  }

  async onVueCreated() {
    this.jwt = Store.getStore().get('jwt')
    // await this.getUser()
    // await this.getService()
  }

  async onClickLeft() {
    this.popScene()
  }

  // async getUser() {
  //   this.showLoading = true;
  //   var data = {
  //     jwt: this.jwt
  //   };
  //   var res = await get(process.env.BASE_API + '/server/login/loaduser', data);
  //   this.showLoading = false;
  //   if (res.data.errcode != 0) {
  //     Toast(res.data.errmsg)
  //     return
  //   }
  //   this.userInfo = res.data.data.user
  //   if (this.userInfo.level == 0) {
  //     // this.userList.splice(2, 1)
  //   } else {
  //     this.userList.splice(3, 1)
  //   }
  // }
  //上传头像
  // async afterRead(file) {
  //   this.showLoading = true;
  //   var data = {
  //     info: file.content
  //   };
  //   var res = await post(process.env.BASE_API + '/server/hall/upload', data);
  //   this.showLoading = false;
  //   if (res.data.errcode != 0) {
  //     Toast(res.data.errmsg)
  //     return
  //   }
  //   this.userInfo.avatar = res.data.src
  //   this.changeInfo()
  // }
  // //修改昵称

  // async beforeClose(action, done) {
  //   if (action === 'confirm') {
  //     if(this.userInfo.name == ""){
  //       Toast("请输入昵称")
  //       done(false);
  //       return
  //     }
  //     this.changeInfo(done)
  //   } else {
  //     done();
  //   }
  // }
  // async cancelNick(){
  //   this.showNick = false
  // }
  // async changeInfo(){
  //   var data = {
  //     jwt: this.jwt,
  //     avatar: this.userInfo.avatar,
  //     name: this.userInfo.name
  //   };

  //   var res = await get(process.env.BASE_API + '/server/login/updateuser', data);

  //   if (res.data.errcode != 0) {
  //     Toast(res.data.errmsg)
  //     return
  //   }
  //   Toast.success("修改成功")
  //   this.showNick = false
  // }

  // async getService() {
  //   var data = {
  //     jwt: this.jwt
  //   };
  //   var res = await get(process.env.BASE_API + '/server/hall/getkefu', data);

  //   if (res.data.errcode != 0) {
  //     Toast(res.data.errmsg)
  //     return
  //   }
  //   this.serviceInfo = res.data.data.data
  // }
  // //确认成为代理
  // async confirm() {
  //   if (this.reason == "") {
  //     Toast("请输入申请理由")
  //     return
  //   }
  //   this.showLoading = true;
  //   var data = {
  //     jwt: this.jwt,
  //     intr: this.reason
  //   };
  //   var res = await get(process.env.BASE_API + '/server/hall/shenqingdaili', data);
  //   this.showLoading = false;
  //   if (res.data.errcode != 0) {
  //     Toast(res.data.errmsg)
  //     return
  //   } 
  //   Toast.success("申请成功")
  // }

  async navigateTo(url) { 
    var self = this
    if(url==''){
      return
    }  

    this.pushScene(url)
  }

}
