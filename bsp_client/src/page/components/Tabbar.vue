<template>
  <div>
    <div style="width: 100%; height: 52px;"></div>
    <van-tabbar>
      <van-tabbar-item v-for="(item, index) in tabList" :key="index" :class="{ active: sonActive == index }" @click="tabTo(item)">
        <span>{{ item.name }}</span>
        <van-icon slot="icon" :name="item.icon" class-prefix="qyc" />
      </van-tabbar-item>
    </van-tabbar>
  </div>
</template>

<script>
export { default } from './Tabbar.js';
</script>

<style lang="less">
@import './Tabbar.less';
</style>
