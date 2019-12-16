<template>
  <div>
    <van-nav-bar fixed left-arrow @click-left="onClickLeft" :border="false"/>
    <div class="qrcode">
      <img :src="qrcode">
       <p>扫描二维码进行注册</p>
    </div>
    
  </div>
</template>
<script>
export { default } from './share.js';
</script>

<style lang="less">
@import '../../assets/public/public.less';
@import '../../assets/iconfont/iconfont.css';
@import './share.less';
</style>
