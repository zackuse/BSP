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
} from 'vant'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util';
import Waiting from '@/page/components/Waiting.vue';
require('@/framework/functions')

@Component({
    props: {
        // methods: Object
    }
})

export default class Feedback extends QScene {
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
            value:'',
            message:''
        }
    }
    onClickLeft() {
        this.popScene()
    }
    async onVueCreated() {
        
    }

}