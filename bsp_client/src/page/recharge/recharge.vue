<template>
  <div>
    <van-nav-bar title="充值" fixed left-arrow @click-left="onClickLeft" />
    <div style="width: 100%; height: 46px;"></div>

    <van-collapse v-model="activeNames">
      <van-collapse-item title="USDT" name="1" :is-link="false" disabled></van-collapse-item>
    </van-collapse>
    <div class="code">
      <div class="codeBox">
        <img :src="qrcode" />
      </div>
      <div class="codeName">充值地址</div>
      <div class="address">
        <div class="wallet">
          <div class="copyTitle">23231{{userInfo.address}}</div>
          <div class="copy" @click="copy">复制地址</div>
        </div>
      </div>
    </div>
    <div class="notice">
      <ul>
        <li>•禁止向地址充值当前币种之外的资产，任何充入当前币种地址的非当前币种资产将不可找回  </li>
        <li> •充值完成后，你可以进入历史页面跟踪进度。历史记录</li>
        <li>•您的充值地址不会经常改变，可以重复充值；如有更改，我们会尽量通过网站公告或邮件通知您。</li>
        <li>•请务必确认电脑及浏览器安全，防止信息被篡改或泄露。</li>
      </ul>
    </div>
  </div>
</template>
<script>
export { default } from "./recharge.js";
</script>

<style lang="less">
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./recharge.less";
</style>
