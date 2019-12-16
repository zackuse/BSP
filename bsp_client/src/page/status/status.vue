<template>
  <div>
    <van-nav-bar title="身份认证" left-arrow @click-left="onClickLeft" />
    <van-cell-group>
      <van-field v-model="value" label="真实姓名" placeholder="请填写真实姓名" />
      <van-field v-model="value" label="身份证号" placeholder="请填写身份证号" />
    </van-cell-group>
    <van-button type="primary" size="large" class="loginBtn">确 认</van-button>
  </div>
</template>

<script>
export { default } from "./status.js";
</script>

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "./status.less";
</style>  