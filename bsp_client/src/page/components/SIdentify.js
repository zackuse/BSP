import {
	QScene
} from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
	get,
	post
} from '@/framework/request'
import {
	strict
} from 'assert'

import {
	Tabbar,
	TabbarItem
} from 'vant'

require('@/framework/functions')
var tenjin = require('tenjin')

@Component({
	props: {
		width: {
		  type: Number,
		  default: 70,
		},
		height: {
		  type: Number,
		  default: 27,
		},
		length: {
		  type: Number,
		  default: 4,
		},
		backgroundColor: {
		  type: String,
		  default: 'transparent',
		},
	},
  name:'SIdentify'
})
export default class SIdentify extends QScene {
	mycomponent() {
		return {
			Tabbar,
			TabbarItem
		}
	}
	data() {
		return {

		}
	}
	render(h) {
	  return (
	    <canvas
	      ref='myCanvas'
	      width={this.width}
	      height={this.height}
	      onClick={this.createCode}
	    >
	    </canvas>
	  )
	}
	mounted(){
		this.createCode()
	}

	async onVueCreated() {

	}

	async refresh() {
		this.createCode()
	}

	async createCode() {
		let code = ''
		const canvas = this.$refs.myCanvas
		const char = Array.of(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
			'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z')

		for (let i = 0, len = this.length; i < len; i++) {
			const charIndex = Math.floor(Math.random() * 36)
			code += char[charIndex]
		}

		if (canvas) {
			const ctx = canvas.getContext('2d')
			ctx.clearRect(0, 0, canvas.width, canvas.height) // 清空画布，防止绘制重叠
			ctx.fillStyle = this.backgroundColor
			ctx.fillRect(0, 0, this.width, this.height)
			ctx.font = '20px arial'

			// 创建渐变
			// const gradient = ctx.createLinearGradient(0, 0, canvas.width, 0)
			// gradient.addColorStop('0', 'magenta')
			// gradient.addColorStop('0.5', 'blue')
			// gradient.addColorStop('1.0', 'red')
			// 用渐变填色
			// ctx.strokeStyle = gradient
			// 画布上添加验证码
			ctx.strokeStyle = '#1198ea'
			ctx.strokeText(code, 5, 20)

			this.$emit('change', code)
		}
	}

}
