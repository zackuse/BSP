<template>
	<div>
		<van-nav-bar title="交易密码" left-arrow @click-left="onClickLeft" fixed />
		<div style="width: 100%; height: 46px;"></div>

		<van-cell-group class="normalGroup">
			<van-field v-model="phone" label="原密码" placeholder="请输入原密码" center/>
			<van-field v-model="loginPwd" label="新密码" type="password" placeholder="请输入新密码" center/>
			<van-field v-model="cLoginPwd" label="再次输入" type="password" placeholder="确认新密码" center/>
			<van-field v-model="smsCode" label="谷歌验证" placeholder="请输入验证码" center/>
				<!-- <van-button slot="button" size="small" class='send' v-show="isGet" @click='send(60)'>发送验证码</van-button> -->
				<van-button slot="button" size="small" class='send' type="primary" v-show="!isGet">{{time}}
				</van-button>
		</van-cell-group>
		<van-button type="primary" size="large" @click="reset" class="loginBtn">确 认</van-button>
	</div>
</template>
<script>
	export { default } from './changeTrade.js';
</script>

<style lang="less">
	@import '../../assets/public/public.css';
	@import '../../assets/public/public.less';
	@import '../../assets/iconfont/iconfont.css';
	@import './changeTrade.less';
</style>