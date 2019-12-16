import { QScene } from '@/framework/QScene.js'
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
    Tab,
    Tabs 
} from 'vant'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util';
import Waiting from '@/page/components/Waiting.vue';
require('@/framework/functions')
@Component({
    props: {
        // methods: Object
    },
    filters: {
        formatDate: function (value) {
        let date = new Date(value);
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
      }
    }

})

export default class Status extends QScene {
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
            Tab,
            Tabs
        }
    }
    data() {
        return {
            showLoading: false,
            showReg: true,
            value:'',
            active: 0,
            formatDate:'',
            time:Date.parse(new Date()),
        }
    }
    onClickLeft() {
        this.popScene()
    }
    async onVueCreated() {
        
    }

}