import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util';
import MyTabbar from '@/page/components/Tabbar.vue'
import Vue from "vue"
import {
  Swipe,
  SwipeItem,
  Icon,
  Toast,
  NavBar,
  Row,
  Col,
  Dialog,
  CellGroup,
  Field,
  Cell,
  Popup,
  Button,
  Tab,
  Tabs,
  DropdownMenu,
  DropdownItem
} from 'vant'
Vue.use(DropdownMenu).use(DropdownItem);
require('@/framework/functions')

@Component({
  props: {

  },

})

export default class PledgeScene extends QScene {
  mycomponent() {
    return {
      Swipe,
      SwipeItem,
      Icon,
      Toast,
      NavBar,
      Row,
      Col,
      Dialog,
      CellGroup,
      Field,
      Cell,
      Popup,
      MyTabbar,
      Button,
      Tab,
      Tabs,
      DropdownMenu,
      DropdownItem
    }
  }
  data() {

    return {
      active: '0',
      value1:0,
      option1:[],
      option1: [
        { text: '下拉选择数量', value: 0 },
        { text: 'BSP', value: 1 },
        { text: '活动商品', value: 2 }
      ],
    }
  }

  async onVueCreated() {

  }

  async navigateTo(url) {
    this.pushScene(url)
  }

  async getUser() {
    this.showLoading = true;

  }


  async inquiry() {
    this.show = true
  }
  async toBusiness() {
    this.pushScene("organism.Business", {

    })
  }
  async onClickLeft() {
    this.popScene()
  }

}
