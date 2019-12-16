<template>
  <div>
    <van-nav-bar title="提现" fixed left-arrow @click-left="onClickLeft" />
    <div style="width: 100%; height: 46px;"></div>
    <div class="normal">
      <van-cell-group class="normalGroup">
        <van-field v-model="num" label="USTD" placeholder="请输入提币金额" />
        <van-field type="password" v-model="tradePwd" placeholder="请输入交易密码" />
        <van-field v-model="address" placeholder="请输入钱包地址" />
        <van-field v-model="sms" center clearable  placeholder="请输入验证码">
          <van-button slot="button" size="small" type="primary">发送验证码</van-button>
        </van-field>
        <van-field v-model="goole" placeholder="请输入谷歌验证码" />
      </van-cell-group>
      <div class="point">
        <p>为保障资金安全，当您账户安全策略变更、密码修改、我们会对提币进行人工审核，请耐心等待工作人员联系。请务必确认电脑及浏览器安全，防止信息被篡改或泄露。</p>
      </div>
    </div>
    <van-button type="primary" size="large" @click="confirm" class="loginBtn">确 认</van-button>
  </div>
</template>
<script>
export { default } from "./withdraw.js";
</script>

<style lang="less">
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./withdraw.less";
</style>
 