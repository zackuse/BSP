<template>
	<div>
		<van-nav-bar title="登录密码重置" left-arrow @click-left="onClickLeft" fixed />
		<div style="width: 100%; height: 46px;"></div>

		<van-cell-group class="normalGroup">
			<van-field v-model="phone" label="手机号" placeholder="请输入手机号" center/>
			<van-field v-model="smsCode" label="验证码" placeholder="请输入短信验证码" center>
				<van-button slot="button" size="small" class='send' v-show="isGet" @click='send(60)'>发送验证码</van-button>
				<van-button slot="button" size="small" class='send' type="primary" v-show="!isGet">{{time}}s
				</van-button>
			</van-field>
			<van-field v-model="loginPwd" label="新密码" type="password" placeholder="请输入登录密码" center/>
			<van-field v-model="cLoginPwd" label="确认新密码" type="password" placeholder="请再次输入密码" center/>
		</van-cell-group>
		<van-button type="primary" size="large" @click="reset" class="loginBtn">确 认</van-button>
	</div>
</template>
<script>
	export { default } from './changeLogin.js';
</script>

<style lang="less">
	@import '../../assets/public/public.css';
	@import '../../assets/public/public.less';
	@import '../../assets/iconfont/iconfont.css';
	@import './changeLogin.less';
</style>