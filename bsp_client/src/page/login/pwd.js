import {QScene} from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
	Button,
	CellGroup,
	Field,
	Icon,
	Toast,
	NavBar
} from 'vant'
import {get} from '@/framework/request'
require('@/framework/functions')
var md5 = require('md5');

@Component({
	props: {

	}
})
export default class PwdScene extends QScene {
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
			phone: "",
			smsCode: "",
			loginPwd: "",
			cLoginPwd: "",
			isGet: true,
			time: 60,
		}
	}
	
	async onVueCreated() {}
	
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
		var t = setInterval(function() {
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
	
	async reset(){
		if(this.phone == ""){
			Toast("请输入手机号")
			return
		}
		if(this.phone == ""){
			Toast("请输入验证码")
			return
		}
		if(this.loginPwd == ""){
			Toast("请输入密码")
			return
		}
		if(this.cLoginPwd == ""){
			Toast("请再次输入密码")
			return
		}
		if(this.cLoginPwd != this.loginPwd){
			Toast("两次输入的密码不一致")
			return
		}
		var data = {
			phone: this.phone,
			code: this.smsCode,
			newpassword: this.loginPwd,
		}
		var res = await get(process.env.BASE_API + '/server/login/forgetpassword', data)
		if (res.data.errcode != 0) {
			Toast(res.data.errmsg)
			return
		}
		Toast.success("密码重置成功")
		await this.wait(1000)
		this.popScene()
	}
}
