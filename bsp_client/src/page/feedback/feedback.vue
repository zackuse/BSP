<template>
  <div>
    <van-nav-bar title="意见反馈" left-arrow @click-left="onClickLeft" />
    <van-cell-group>
      <van-field v-model="value"  placeholder="反馈主题" />
       <van-field
    v-model="message"
    rows="2"
    autosize
    type="textarea"
    placeholder="反馈内容"
    show-word-limit
  />
    </van-cell-group>
    <van-button type="primary" size="large" class="loginBtn">反 馈</van-button>
  </div>
</template>

<script>
export { default } from "./feedback.js";
</script>

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "./feedback.less";
</style>  