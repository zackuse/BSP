<template>
  <div>
    <van-nav-bar title="谷歌绑定" left-arrow @click-left="onClickLeft" fixed />
    <div style="width: 100%; height: 46px;"></div>
    <div class="point">
      <p>1、在应用商店中搜索“Goole Authentication”应用并下载</p>
      <p>2、安装完成后打开Goole Authentication，点击右上角（安卓为右下角）的“+”，选择“手动输入密钥”，页面中填入下方密钥</p>
    </div>
    <van-cell-group>
      <van-field value="V4FKJR4X2GNDNDSF" label="复制" disabled class="posit"/>
    </van-cell-group>
	<div class="point">
	<p >3、添加成功后，您将得到一个有一定时效的6位数验证码，请将该验证码填入下方输入框中（需要在验证码时效内）</p>
	</div>
	  <van-cell-group>
	  <van-field  placeholder="谷歌验证码" />
    </van-cell-group>
    <van-button type="primary" size="large" class="loginBtn">立即绑定</van-button>
  </div>
</template>
<script>
export { default } from "./changeBD.js";
</script>

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./changeBD.less";
</style>