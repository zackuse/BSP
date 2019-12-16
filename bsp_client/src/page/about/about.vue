<template>
  <div>
    <van-nav-bar title="关于我们" fixed left-arrow @click-left="onClickLeft" />
    <div style="width: 100%; height: 46px;"></div>
    <div class="title">{{detail.title}}</div>
    <div class="content" v-html="detail.content">{{detail.content}}</div>
    
  </div>
</template>
<script>
export { default } from "./about.js";
</script>

<style lang="less">
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./about.less";
</style>
