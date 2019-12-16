<template>
  <div>
    <van-nav-bar title="记录" left-arrow @click-left="onClickLeft" />
    <van-tabs v-model="active">
      <van-tab title="全部"></van-tab>
      <van-tab title="充值"></van-tab>
      <van-tab title="提现"></van-tab>
    </van-tabs>
    <div>
      <van-cell-group>
        <van-cell :title="time | formatDate" value="充值"  size="large"/>
        <van-cell title="操作数量:" class="quantity">
          <p slot="default">324 <span>(USDT)</span></p>
        </van-cell>
        <van-cell  value="区块已完成" class="accomplish"/>
      </van-cell-group>
    </div>
  </div>
</template>

<script>
export { default } from "./take.js";
</script>

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "./take.less";
</style>  