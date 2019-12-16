<template>
  <div class="lineBox" v-show="isShow == 0">
    <div class="lineTitle">价格走势</div>
    <div id="main"></div>
  </div>
</template>

<script>
export { default } from './PriceTrend.js';
</script>

<style lang="less">
@import '../../assets/public/public.less';
@import './PriceTrend.less';
</style>
