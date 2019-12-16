class login {
  constructor (handler) {
    this.auths = {}
    this.handler = handler
  }

  init () {
    var self = this
    if (window.plus) {
      self.plusReady()
    } else {
      document.addEventListener('plusready', self.plusReady, false)
    }
  }

  plusReady () {
    var self = this
    plus.oauth.getServices(function (services) {
      for (var i in services) {
        var service = services[i]
        self.auths[service.id] = service
      }
    }, function (e) {
      Toast('获取登录认证失败：' + e.message)
    })
  }

  loginById (id) {
    console.log('需在vue绑定对应函数-->on_login_success 和 on_login_error')
    if (window.plus) {
      var self = this
      var auth = self.auths[id]
      if (auth) {
        var w = null
        if (plus.os.name == 'Android') {
          w = plus.nativeUI.showWaiting()
        }
        document.addEventListener('pause', function () {
          setTimeout(function () {
            w && w.close()
            w = null
          }, 2000)
        }, false)
        auth.login(function () {
          w && w.close()
          w = null
          // Toast("登录认证成功：");
          // console.log(JSON.stringify(auth.authResult));
          self.userinfo(auth)
        }, function (e) {
          w && w.close()
          w = null
          Toast('登录认证失败：')
          console.log('[' + e.code + ']：' + e.message)
        })
      } else {
        Toast('无效的登录认证通道！')
      }
    } else {
      var fname = 'on_loginh5_success'
      var self = this
      var f = self.handler[fname]
      if (f) {
        f(id)
      } else {
        console.error('没有找到该方法:' + fname)
      }
    }
  }

  userinfo (a) {
    var self = this
    a.getUserInfo(function () {
      var fname = 'on_login_success'

      var f = self.handler[fname]
      if (f) {
        f(a)
      } else {
        console.error('没有找到该方法:' + fname)
      }
    }, function (e) {
      var fname = 'on_login_error'

      var f = self.handler[fname]
      if (f) {
        f(e)
      } else {
        console.error('没有找到该方法:' + fname)
      }
    })
  }
}

export {
  login
}
