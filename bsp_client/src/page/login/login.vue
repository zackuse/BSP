<template>
  <div>
    <img src="../../assets/images/login/logo.png" class="logoImg" />
    <img src="../../assets/images/login/wenzi.png" class="wenziImg" />
    <van-cell-group class="loginGroup">
      <van-field v-model="phone" placeholder="请输入手机号" />
      <van-field v-model="password" placeholder="请输入密码" type="password" />
      <van-field v-model="yzcode" placeholder="验证码">
        <van-button slot="button" size="small" type="primary" class="send">1234</van-button>
      </van-field>
    </van-cell-group>
    <van-button type="primary" size="large" @click="toLogin" class="loginBtn">登录</van-button>
    <div class="pwd" @click="toPwd"> 您要找回密码？</div>
    <div @click="toReg" class="reg">没有注册账号？<span>注册</span></div>
    <Waiting v-show="showLoading"></Waiting>
  </div>
</template>
<script>
export { default } from "./login.js";
</script>

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./login.less";
</style>
