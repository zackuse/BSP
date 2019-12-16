<template>
  <div>
    <van-nav-bar title="订单详情" fixed left-arrow @click-left="onClickLeft" />
    <div style="width: 100%; height: 46px;"></div>
    <van-cell-group class="normalGroup">
      <van-cell title="订单ID" :value="order.orderid" readonly />
      <van-cell title="单值" :value="orderNum" readonly />
      <van-cell title="接单ID" :value="order.uid" readonly />
      <van-cell title="时间" :value="order.createtime | formatDate" readonly />
      <!-- <van-cell title="打款地址" :value="order.address" readonly /> -->
      <van-cell title="接单方式" :value="order.paytype | formatType" readonly />
      <van-cell title="接单账号" :value="order.payaccount" readonly />
      <van-cell title="提成比例" :value="order.yongjin+'%'" readonly />
      <van-cell title="本单佣金" :value="yongjin" readonly />
      <van-cell title="接单状态" value="已放行" readonly class="status" v-if="order.status == 1"/>
      <van-cell title="接单状态" value="无效订单" readonly class="status" v-if="order.status == 2"/>
      <van-cell title="接单状态" value="异常订单" readonly class="status" v-if="order.status == 4"/>
    </van-cell-group>
    <div class="bottomBtn" v-if="order.status == 0">
      <van-button type="default" @click="refuse">无效订单</van-button>
      <van-button type="default" color="#ff5521" @click="abnormal">异常订单</van-button>
      <van-button type="default" @click="agree">确认放行</van-button>
    </div>

    <!-- 同意接单 -->
    <van-dialog
      v-model="showAgree"
      title="确认放行"
      :showConfirmButton="false"
      :closeOnClickOverlay="true"
    >
      <div class="agreeTitle">
        <van-icon class-prefix="qyc" name="dengpao" />接单成功将扣除您
        <span> {{parseFloat(orderStp).toFixed(2)}} STP</span>
        ≈{{parseFloat(orderUsdt).toFixed(4)}} USDT
      </div>
      <van-field v-model="tradePwd" placeholder="请输入交易密码" type="password" />
      <div class="tishi">
        <p>提示：</p>
        <p>请确认你的收款账户是否<span>已到账</span>并且到账<span>金额无误</span>，点击<span>确认放行</span>后
        即表示你同意从你的账户<span>扣除</span>相应的USDT，此操作无法撤回</p>
      </div>
      <van-button type="primary" size="large" @click="confirmAgree" class="loginBtn">确认</van-button>
    </van-dialog>
  </div>
</template>
<script>
export { default } from "./orderDetail.js";
</script>

<style lang="less">
@import "../../assets/public/public.less";
@import "../../assets/iconfont/iconfont.css";
@import "./orderDetail.less";
</style>
