import { QScene } from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
    Button,
    CellGroup,
    Field,
    Icon,
    Toast,
    NavBar
} from 'vant'
import { get } from '@/framework/request'
import { Store } from '@/framework/utils/util';
require('@/framework/functions')
var md5 = require('md5');
@Component({
    props: {

    }
})
export default class ChangeBD extends QScene {
    mycomponent() {
        return {
            Button,
            Field,
            CellGroup,
            Icon,
            Toast,
            NavBar
        }
    }
    data() {
        return {

        }
    }

    async onVueCreated(){
        
    }
    async onClickLeft(){
        this.popScene()
    }
}
