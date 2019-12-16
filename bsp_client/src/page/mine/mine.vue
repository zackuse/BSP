<template>
  <div>
    <div class="topBox">
      <div class="avatar">
        <img src="../../assets/images/mine/touxiang.png" alt />
        <div class="userInfo">
          <div>昵称：用户名</div>
          <div>等级：初级</div>
          <div>时光机类型：初级时光机</div>
        </div>
      </div>
    </div>
    <van-cell-group class="payGroup">
      <van-cell
        :title="item.title"
        :is-link="item.bool"
        center
        v-for="(item,index) in userList"
        :key="index"
        @click="navigateTo(item.url)"
        :value="item.value"
      >
        <img :src="item.img" slot="icon" class="iconImg" /> 
      </van-cell>
    </van-cell-group>

    <Waiting v-show="showLoading"></Waiting>
    <MyTabbar :sonActive="tabbarActive" :currentId="currentId"></MyTabbar>
  </div>
</template>
<script>
export { default } from "./mine.js";
</script> 

<style lang="less">
@import "../../assets/public/public.css";
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "mine.less";
</style>
