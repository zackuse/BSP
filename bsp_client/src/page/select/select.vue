<template>
  <div>
    <van-nav-bar title="选择时光机" fixed />
    <div style="width: 100%; height: 46px;"></div>
    <div class="titleBox">
      <span>—</span> BSP合伙人计划
      <span>—</span>
    </div>
    <div class="coinList">
      <div class="BSP-tell">
        在 BSP 通证经济模型中，我们设计的挖矿时间为 17 年，
        也就意味着所有的生态参与者，可以获得长达 17 年的挖
        矿收益，因此在 BSP 生态中，我们引入了时光机的概念，
        我们可以在 BSP 平台置换时光机，在区块链行业即将迎来
        大爆发的 20 年，带领我们穿越时空，通过时间的累积，
        快速抵达财富的宇宙之巅
      </div>
      <div class="coinItem">
        <img src="../../assets/images/ecology/btc.png" />
        <span class="coin">BSP初级时光机</span>
        <van-button type="default" class="query">立即开启</van-button>
      </div>
      <div class="coinItem">
        <img src="../../assets/images/ecology/eth.png" />
        <span class="coin">BSP高级时光机</span>
        <van-button type="default" class="query">立即开启</van-button>
      </div>
      <div class="coinItem">
        <img src="../../assets/images/ecology/eos.png" />
        <span class="coin">BSP超级时光机</span>
        <van-button type="default" class="query">立即开启</van-button>
      </div>
    </div>
    <div class="titleBox last-title">
      <span>—</span> 排行榜
      <span>—</span>
    </div>
    <div class="coinList ranking-list">
      <div class="coinItem ranking">
        <p class="text">排名</p>
        <p class="coin text">昵称</p>
        <p class="text">总BSP穿梭力</p>
      </div>

      <div class="coinItem ranking size" v-for="(data,index) in list" :key="index">
        <p class="text num">
          <img v-if="data.judge==0" :src="data.img" alt />
          <span v-else>{{data.num}}</span>
        </p>
        <p class="flex text">{{data.userName}}</p>
        <p class="text">{{data.BSP}}</p>
      </div>
    </div>
  </div>
</template>
<script>
export { default } from "./select.js";
</script>

<style lang="less">
@import "../../assets/public/public.less";
@import "../../assets/public/public.css";
@import "../../assets/iconfont/iconfont.css";
@import "./select.less";
</style>
