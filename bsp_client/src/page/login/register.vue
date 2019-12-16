<template>
  <div>
		<van-nav-bar left-arrow @click-left="onClickLeft" fixed class="regNav"/>
    <div style="width: 100%; height: 46px;"></div>
    <div class="title">账户注册</div>
    <van-cell-group class="loginGroup">
      <van-field v-model="phone" placeholder="请输入手机号"/>
      <van-field v-model="nick" placeholder="请输入昵称"/>
      <van-field v-model="logPwd" placeholder="请输入密码" type="password"/>
      <van-field v-model="conLogPwd" placeholder="请重复密码" type="password"/>
      <van-field v-model="tradePwd" placeholder="请输入交易密码" type="password"/>
      <van-field v-model="conTradePwd" placeholder="请重复交易密码" type="password"/>
      <van-field v-model="smsCode" placeholder="请输入验证码" center>
        <van-button slot="button" size="small" class="getCode" v-show="isGet" @click="send(60)">获取验证码</van-button>
        <van-button slot="button" size="small" class="getCode" type="primary" v-show="!isGet">{{time}}s</van-button>
      </van-field>
      <van-field v-model="tjPhone" placeholder="请输入邀请码"/>
    </van-cell-group>
    <van-button type="primary" size="large" @click="toReg" class="loginBtn">注 册</van-button>
  </div>
</template>
<script>
export { default } from "./register.js";
</script>

<style lang="less">
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./register.less";
</style>
