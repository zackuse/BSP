<template>
  <div>
    <van-nav-bar title="个人资料" left-arrow @click-left="onClickLeft" />
    <van-cell-group>
      <van-cell title="昵称" value="用户名" />
      <van-cell title="时光机类型" value="初级时光机" />
      <van-cell title="会员等级" value="初级" />
      <van-cell title="头像" value class="line" center>
        <img src="../../assets/images/mine/miniTX.png" slot="default" />
      </van-cell>
      <van-cell title="交易密码" is-link @click="changeLog" />
    </van-cell-group>
    <van-button class="loginBtn" type="primary" size="large"   @click="navigateTo(item.url)">退出登录</van-button>
  </div>
</template>

<script>
export { default } from "./indiv.js";
</script>

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "./indiv.less";
</style> 