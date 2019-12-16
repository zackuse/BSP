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
			jwt: ''
		}
	}

	async onVueCreated() {
		this.jwt = Store.getStore().get('jwt')
	}

	async onClickLeft() {
		this.popScene()
	}

	async send(time) {
		if (this.phone == '') {
			Toast("请输入原密码")
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

	async reset() {
		if (this.phone == "") {
			Toast("请输入原密码")
			return
		}
		if (this.phone == "") {
			Toast("请输入验证码")
			return
		}
		if (this.loginPwd == "") {
			Toast("请输入密码")
			return
		}
		if (this.cLoginPwd == "") {
			Toast("请再次输入密码")
			return
		}
		if (this.cLoginPwd != this.loginPwd) {
			Toast("两次输入的密码不一致")
			return
		}
		var data = {
			phone: this.phone,
			code: this.smsCode,
			password: this.loginPwd,
			passwordnew: this.cLoginPwd,
			jwt: this.jwt
		}
		var res = await get(process.env.BASE_API + '/server/login/updatejypwd', data)
		if (res.data.errcode != 0) {
			Toast(res.data.msg)
			return
		}
		Toast("交易密码重置成功")
	}
}
