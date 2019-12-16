import { QScene } from '@/framework/QScene.js'
import Vue from 'vue'
import Component from 'vue-class-component'
import {
    Button,
    CellGroup,
    Cell,
    Field,
    Icon,
    Toast,
    Dialog,
    NavBar,
} from 'vant'
Vue.use(Dialog);
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util';
import Waiting from '@/page/components/Waiting.vue';
require('@/framework/functions')

@Component({
    props: {
        // methods: Object
    }
})

export default class Indiv extends QScene {
    mycomponent() {
        return {
            Button,
            Field,
            CellGroup,
            Cell,
            Icon,
            Toast,
            Dialog,
            Waiting,
            NavBar,
        }
    }
    data() {
        return {
            showLoading: false,
            showReg: true,
            tx: [{ img: require('../../assets/images/mine/miniTX.png') }],
            img: [],
            retreat: [{
                title: '退出登录',
                url: 'index'
            }],
            item:[]
        }
    }
    onClickLeft() {
        this.popScene()
    }
    async onVueCreated() {
        this.img = this.tx[0];
        this.item=this.retreat[0]
        
    }

    async changeLog() {
        this.pushScene('changePwd.changeTrade')

    }
    async navigateTo(url) {
        if (url == 'index') {
            Dialog.confirm({
                title: '提示',
                message: '确认退出？'
            }).then(() => {
                this.outLogin()
            }).catch(() => { })
            return
        }
    }
     async outLogin() {
    Toast('退出成功')
    await this.wait(1000)
    Store.getStore().remove('jwt')
    this.replaceScene('login.login')
  }
}