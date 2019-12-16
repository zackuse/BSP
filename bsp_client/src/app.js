import {QApp} from '@/framework/QApp.js'
import Component from 'vue-class-component'
import { Collapse, CollapseItem, Toast, Row, Col, Cell} from 'vant'
import {
  Store
} from '@/framework/utils/util';
@Component({
  props: {
    // methods: Object
  }
})

export default class App extends QApp {

  mycomponent () {
    return {
      Collapse,
      CollapseItem,
      Toast,
      Row,
      Col,
      Cell,
    }
  }

  data () {
    return {
      activeNames: [''],
      a:1,
      Lang:{},
      user: {}
    };
  }

  async onVueCreated () {
    this.user = Store.getStore().get('user')
    if(this.user){
      this.pushScene('home.home', {
        showNotice: 1
      })
      return
    }
    this.pushScene('login.login')
  }
}
