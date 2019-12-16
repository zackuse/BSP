<template>
  <div>
    <van-nav-bar title="生态" fixed />
    <div style="width: 100%; height: 46px;"></div>
    <van-swipe :autoplay="3000" indicator-color="white">
      <van-swipe-item>
        <img src="../../assets/images/ecology/banner.png" class="swipeImg">
      </van-swipe-item>
    </van-swipe>
    <div class="titleBox"><span>—</span> BSP “一篮子”计划通证 <span>—</span></div>
    <div class="coinList">
      <div class="coinItem">
        <img src="../../assets/images/ecology/btc.png">
        <span class="coin">BTC：100</span>
        <van-button type="default" class="query">查询</van-button>
      </div>
      <div class="coinItem">
        <img src="../../assets/images/ecology/eth.png">
        <span class="coin">ETH：100</span>
        <van-button type="default" class="query">查询</van-button>
      </div>
      <div class="coinItem">
        <img src="../../assets/images/ecology/eos.png">
        <span class="coin">EOS：100</span>
        <van-button type="default" class="query">查询</van-button>
      </div>
    </div>
    <div class="titleBox"><span>—</span>&nbsp;&nbsp;&nbsp;&nbsp; BSP生态计划 &nbsp;&nbsp;&nbsp;&nbsp;<span>—</span></div>
    <van-grid :column-num="3" :border="true">
      <van-grid-item v-for="(item,index) in planList" :key="index" @click="toUrl(item.url)">
        <img :src="item.icon" class="planImg" />
        <div class="text">{{item.name}}</div>
      </van-grid-item>
    </van-grid>
    <div class="titleBox"><span>—</span>&nbsp;&nbsp;&nbsp;&nbsp; BSP一站式服务 &nbsp;&nbsp;&nbsp;&nbsp;<span>—</span></div>
    <van-grid :column-num="3" :border="true">
      <van-grid-item v-for="(item,index) in serviceList" :key="index" @click="toUrl(item.url)">
        <img :src="item.icon" class="planImg" />
        <div class="text">{{item.name}}</div>
      </van-grid-item>
    </van-grid>
    <MyTabbar :sonActive="tabbarActive" :currentId="currentId"></MyTabbar>
  </div>
</template>
<script>
export { default } from "./ecology.js";
</script>

<style lang="less">
@import "../../assets/public/public.less";
@import "../../assets/public/public.css";
@import "../../assets/iconfont/iconfont.css";
@import "./ecology.less";
</style>
