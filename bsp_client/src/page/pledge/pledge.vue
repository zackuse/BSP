<template>
  <div>
    <van-nav-bar
      title="质押"
      left-arrow
      @click-left="onClickLeft"
    />
    <van-tabs v-model="active">
  <van-tab title="升级"></van-tab>
  <van-tab title="降级"></van-tab>
</van-tabs>
<div class="pledge-bg">
  <div class="pledge-box">
<p>质押数量</p>
<van-dropdown-menu>
  <van-dropdown-item v-model="value1" :options="option1" />
    <van-icon name="arrow-down" />
</van-dropdown-menu>
<div class="red">注：</div>
<div class="red">单位: BSP，每增加100BSP，增加1倍的穿梭力，同时增加币 龄穿梭力2S/天。</div>
<div class="btn-box">
<van-button type="primary" size="large" class="affirm">确认质押</van-button>
</div>
</div>
</div> 
  </div>
</template>

<script>
export { default } from "./pledge.js";
// export
</script>
 
<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./pledge.less";
</style>