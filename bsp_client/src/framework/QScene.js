import Vue from 'vue'
import Component from 'vue-class-component'
import {
  Dialog,
  Toast
} from 'vant';
Vue.use(Dialog).use(Toast);
import {
  QObj
} from '@/framework/QObj'
import {
  Util
} from '@/framework/utils/util'
const URI = require('urijs')

@Component({})
class QScene extends QObj {
  async created() {
    console.log('created')
    var comps = this.mycomponent()
    var entries = Object.entries(comps)
    entries.forEach(element => {
      let v = element[1]
      if (v.extendOptions && v.extendOptions.name) {
        Vue.component(v.extendOptions.name, v)
      } else {
        Vue.component(v.name, v)
      }
    })

    this.stack = []
    await this.onVueCreated()
    var self = this
    if (Util.isNative()) {
      window.addEventListener('Core', function(event) {
        switch (event.detail.type) {
          case 'Resume':
            if (self.onResume) {
              self.onResume(event.detail)

            }
            break
        }

      })
      document.addEventListener('touchstart', function() {
        return false;
      }, true);
      var as = 'pop-in';

      function plusReady() {
        plus.webview.currentWebview().setStyle({
          scrollIndicator: 'none'
        });
        plus.key.addEventListener('backbutton', eventBackButton, false);
      }
      var first = null;

      function eventBackButton() {

        if (self.sceneCount() == 1) {
          if (!first) {
            first = new Date().getTime();
            Toast('再按一次退出应用');
            setTimeout(function() {
              first = null;
            }, 2000);
          } else {
            if (new Date().getTime() - first < 2000) {
              plus.runtime.quit();
            }
          }
        } else {
          first = null
          self.popScene()
        }

      }
      if (window.plus) {
        plusReady();
      } else {
        document.addEventListener('plusready', plusReady, false);
      }
    }
  }

  wait(delay) {
    return new Promise(function(resolve) {
      setTimeout(resolve, delay)
    })
  }

  replaceScene(id, params = {}, aniShow = 'slide-in-right', style = {}, ) { //退出登录
    var self = this
    if (Util.isNative()) {
      var config = {
        url: './' + id + '.html',
        id: id + new Date().getTime()
      }
      style.titleNView = null
      params.id = id
      var extras = {
        params: params
      }

      var wvs = plus.webview.all()

      var wv = plus.webview.create(
        config.url,
        config.id, {
          top: 0, // 新页面顶部位置
          bottom: 0, // 新页面底部位置
          render: 'always',
          popGesture: 'hide',
          bounce: 'none',
          bounceBackground: '#efeff4',
          titleNView: {
            // 详情页原生导航配置
            backgroundColor: '#f7f7f7', // 导航栏背景色
            titleText: config.title, // 导航栏标题
            titleColor: '#000000', // 文字颜色
            type: 'transparent', // 透明渐变样式
            autoBackButton: true, // 自动绘制返回箭头
            splitLine: {
              // 底部分割线
              color: '#cccccc'
            }
          },
          ...style
        },
        extras
      )
      var w = plus.nativeUI.showWaiting()
      // 监听窗口加载成功
      wv.addEventListener(
        'loaded',
        function() {
          wv.show(aniShow) // 显示窗口
          w.close()
          w = null

          for (var i = 0; i < wvs.length; i++) {
            wvs[i].close()
          }
        },
        false
      )
    } else {
      var extras = {}
      extras.stack = []
      params.id = id
      extras.params = JSON.stringify(params)

      var uri = new URI(window.location.href)
      uri.filename(id + '.html')
      uri.search(extras)
      window.location.href = uri.toString()
    }
  }

  pushScene(id, params = {}, aniShow = 'slide-in-right', style = {}) { //打开页面
    console.log('native =', Util.isNative())
    if (Util.isNative()) {
      var config = {
        url: './' + id + '.html',
        id: id + new Date().getTime()
      }
      style.titleNView = null
      params.id = id
      openWebview(config, style, {
        params: params
      })

    } else {
      var uri0 = new URI(window.location.href)
      var filename = uri0.filename()
      var curID = filename.split('.').slice(0, -1).join('.')

      var p = uri0.search(true)
      var stack = []

      if (p.stack) {
        if (Array.isArray(p.stack)) {
          stack = p.stack
        } else {
          stack.push(p.stack)
        }
      }

      stack.push(curID)
      var extras = {}
      params.id = id
      extras.stack = stack
      extras.params = JSON.stringify(params)

      var uri = new URI(window.location.href)
      uri.filename(id + '.html')
      uri.search(extras)
      window.location.href = uri.toString()
    }
  }

  popScene() { //关闭页面
    if (this.sceneCount() < 2) {
      return
    }

    if (Util.isNative()) {
      var allWebview = plus.webview.all()
      var secWs = allWebview[allWebview.length - 2]
      // secWs.show()

      var ws = plus.webview.getTopWebview()

      var uri0 = new URI(ws.getURL())
      var filename0 = uri0.filename()
      var a = filename0.replace('\.html', '')
      ws.close()

      fire(secWs, 'Core', {
        type: 'Resume',
        popId: a
      })
    } else {
      var uri0 = new URI(window.location.href)
      var p = uri0.search(true)
      var id = null

      var stack = []
      if (p.stack) {
        if (Array.isArray(p.stack)) {
          id = p.stack.pop()
          if (p.stack.length > 0) {
            stack = p.stack
          }
        } else {
          id = p.stack
        }
      }

      var extras = {}
      extras.stack = stack
      // extras.params = JSON.stringify(params)

      var uri = new URI(window.location.href)
      uri.filename(id + '.html')
      uri.search(extras)
      window.location.href = uri.toString()
      // setTimeout(function () {
      //   window.location.href = uri.toString()
      // }, 6000)
    }
  }

  getParams() {
    if (Util.isNative()) {
      var wv = plus.webview.currentWebview()
      return wv.params
    } else {
      var uri0 = new URI(window.location.href)
      var p = uri0.search(true)
      if (p.params) {
        return JSON.parse(p.params)
      } else {
        return {}
      }
    }
  }

  sceneCount() {
    if (Util.isNative()) {
      return plus.webview.all().length
    } else {
      var uri0 = new URI(window.location.href)
      var p = uri0.search(true)

      var count = 1
      if (p.stack) {
        if (Array.isArray(p.stack)) {

          if (p.stack.length > 0) {
            count += p.stack.length
          }
        } else {
          count += 1
        }
      }
      return count
    }
  }
}

/**
 * 打开一个webview窗口
 */
function openWebview(config, style = {}, extras = {}, aniShow = 'slide-in-right') {
  var wv = plus.webview.create(
    config.url,
    config.id, {
      top: 0, // 新页面顶部位置
      bottom: 0, // 新页面底部位置
      render: 'always',
      popGesture: 'hide',
      bounce: 'none',
      bounceBackground: '#efeff4',
      titleNView: {
        // 详情页原生导航配置
        backgroundColor: '#f7f7f7', // 导航栏背景色
        titleText: config.title, // 导航栏标题
        titleColor: '#000000', // 文字颜色
        type: 'transparent', // 透明渐变样式
        autoBackButton: true, // 自动绘制返回箭头
        splitLine: {
          // 底部分割线
          color: '#cccccc'
        }
      },
      ...style
    },
    extras
  )
  var w = plus.nativeUI.showWaiting()
  // 监听窗口加载成功
  wv.addEventListener(
    'loaded',
    function() {
      wv.show(aniShow) // 显示窗口
      w.close()
      w = null
    },
    false
  )

  return wv
}

// webview.open  打开得很快 但是不能传参
function openWebviewFast(url, id, title) {
  plus.nativeUI.showWaiting('加载中')
  var ws = plus.webview.open(
    url,
    id, {
      titleNView: {
        backgroundColor: '#f7f7f7', // 导航栏背景色
        titleText: title, // 导航栏标题
        titleColor: '#666', // 文字颜色
        // type: "transparent", // 透明渐变样式
        autoBackButton: true, // 自动绘制返回箭头
        splitLine: {
          // 底部分割线
          color: '#cccccc'
        }
      }
    },
    'slide-in-right',
    420,
    function() {
      plus.nativeUI.closeWaiting()
    }
  )

  return ws
}
// 预加载页面 速度很快,但是不要加载超过10个
function preLoad(webviews = []) {
  webviews.map(webview => {
    const fullExtras = {
      webviewPreload: true,
      ...webview.extras
    }
    plus.webview.create(
      webview.url,
      webview.id, {
        top: 0, // 新页面顶部位置
        bottom: 0, // 新页面底部位置
        render: 'always',
        popGesture: 'hide', // 窗口侧滑返回功能
        bounce: 'none', // 窗口触底是否反弹“none”不反弹
        bounceBackground: '#efeff4',
        titleNView: {
          // 详情页原生导航配置
          backgroundColor: '#f7f7f7', // 导航栏背景色
          titleText: webview.title, // 导航栏标题
          titleColor: '#000000', // 文字颜色
          type: 'transparent', // 透明渐变样式
          autoBackButton: true, // 自动绘制返回箭头
          splitLine: {
            // 底部分割线
            color: '#cccccc'
          }
        },
        ...webview.style
      },
      fullExtras
    )
  })
}

function fire(webview, eventType, data) {
  webview &&
    webview.evalJS(
      `
  document.dispatchEvent(new CustomEvent("${eventType}", {
    detail:${JSON.stringify(data)},
    bubbles: true,
    cancelable: true
  }));
  `
    )
};

export {
  QScene
}
