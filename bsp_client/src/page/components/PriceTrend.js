import {
  QScene
} from '@/framework/QScene.js'
import Component from 'vue-class-component'
import {
  get,
  post
} from '@/framework/request'
import {
  strict
} from 'assert'
import {
  timingSafeEqual
} from 'crypto'
import echarts from 'echarts'
import {
  Store
} from '@/framework/utils/util';

import {
  Toast,
  Picker,
  Popup
} from 'vant'

require('@/framework/functions')
var tenjin = require('tenjin')
console.log()
@Component({
  props: ['isShow'],
  name:'PriceTrend'
})
export default class PriceTrend extends QScene {
  mycomponent() {
    return {
      Toast,
      Picker,
      Popup
    }
  }
  data() {
    return {
      show: true,
      columns: [],
      user: {},
      trend_time: [],
      price: []
    }
  }
  mounted() {
    this.getTrend()
  }

  async onVueCreated() {
    this.user = Store.getStore().get('user')
  }

  async getTrend() {
    this.echart()
    var res = await get(process.env.BASE_API + '/sun/coin_trend', this.user)
    res.data.data.map(v => {
    	this.trend_time.push(v.day)
      this.price.push(v.after_price)
    	// price: v.after_price,
    })

    this.myChart.setOption(this.option)
  }

  async echart() {
    var self = this
    this.myChart = echarts.init(document.getElementById('main'))
    this.option = {
      title: {
        textStyle: {
          color: 'red',
          fontStyle: 'normal',
          fontWeight: 'normal',
          fontSize: '20px',
        }
      },
      grid: {
        left: '5%',
        right: '10%',
        bottom: '3%',
        top: '8%',
        containLabel: true
      },
      tooltip: {},
      xAxis: {
        type: 'category',
        boundaryGap: false,
        axisLine: {
          lineStyle: {
            color: '#cccccc',
            width: 1, //这里是为了突出显示加上的
          }
        },
        // data: [0, 1, 2, 3]
        data: self.trend_time
      },
      yAxis: {
        type: 'value',
        axisLine: {
          lineStyle: {
            color: '#cccccc',
            width: 1, //这里是为了突出显示加上的
          }
        },
        splitLine: {
          lineStyle: {
            // 使用深浅的间隔色（横线颜色）
            color: ['#f4f2e6']
          }
        },
        nameTextStyle: {
          color: ['#c9a980']
        },

        data: [1, 2, 3, 4]

      },
      series: [{
        type: 'line',
        itemStyle: {
          normal: {
            color: '#1198ea', //圈的颜色
            lineStyle: {
              color: '#1198ea' //折线颜色
            }
          }
        },
        // areaStyle: {},
        data: self.price
      }]
    }


  }
}
