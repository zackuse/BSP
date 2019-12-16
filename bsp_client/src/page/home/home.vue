<template>
  <div>
    <van-nav-bar title="BSP" fixed />
    <div style="width: 100%; height: 46px;"></div>
    <div class="homeTop">
      <van-grid :gutter="1" :column-num="2">
        <van-grid-item v-for="(item,index) in homeList" :key="index">
          <p class="name">{{item.name}}</p>
          <p class="number">{{item.number}} {{item.unit}}</p>
        </van-grid-item>
      </van-grid>
    </div>
    <div class="earth">
      <div class="earth1"></div>
      <!-- <img src="../../assets/images/home/1.gif" class="earthImg" /> -->
    </div>
    <van-notice-bar text="通知内容"/>
    <MyTabbar :sonActive="tabbarActive" :currentId="currentId"></MyTabbar>
  </div>
</template>
<script>
export { default } from "./home.js";
</script>

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./home.less";
</style>
