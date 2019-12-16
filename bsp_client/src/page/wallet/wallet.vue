<template>
  <div>
    <van-nav-bar title="资产" fixed />
    <div style="width: 100%; height: 46px;"></div>
    <div class="moneyBox">
      <div class="title">
        <span>总资产</span>
        <div @click="changeMoney">
          <van-icon name="z" class-prefix="qyc" color="#46BEA3" v-show="showMoney" />
          <van-icon name="b" class-prefix="qyc" color="#999999" v-show="!showMoney" />
        </div>
      </div>
      <div class="money">
        <span>{{showMoney?usdt:'****'}}</span>(usdt)
      </div>
      <div class="cny">≈{{showMoney?cny:'****'}} CNY</div>
    </div>
    <ul class="list">
      <li v-for="(item,index) in list" :key="index" @click="skipClick(item.url)">
        <img :src="item.img" />
        <p>{{item.title}}</p>
      </li>
    </ul>
    <div class="coinList"> 
      <div class="coinItem">
        <img src="../../assets/images/wallet/bsp3.png">
        <div class="coinCenter">
          <p>BSP</p>
          <P>Biosphere protocol</P>
        </div>
        <div class="coinRight">
          <p>$ 500</p>
          <p>≈ 5000.00CNY</p>
        </div>
      </div>
      
    </div>
    <MyTabbar :sonActive="tabbarActive" :currentId="currentId"></MyTabbar>
  </div>
</template>
<script>
export { default } from "./wallet.js";
</script>

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./wallet.less";
</style>
