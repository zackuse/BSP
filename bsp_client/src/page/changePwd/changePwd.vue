<template>
  <div>
    <van-nav-bar title="安全中心" fixed left-arrow @click-left="onClickLeft" />
    <div style="width: 100%; height: 46px;"></div>
    <van-cell-group>
      <van-cell title="谷歌验证" is-link @click="changeBD"/>
    </van-cell-group>
  </div>
</template>
<script>
export { default } from './changePwd.js';
</script>

<style lang="less">
@import '../../assets/public/public.less';
@import '../../assets/iconfont/iconfont.css';
@import './changePwd.less';
</style>
 