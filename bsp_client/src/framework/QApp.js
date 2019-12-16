import Vue from 'vue'
import Component from 'vue-class-component'
import {
  Dialog,
  Toast
} from 'vant';
Vue.use(Dialog).use(Toast);
import {
  QScene
} from '@/framework/QScene'
import {
  Util
} from '@/framework/utils/util'
const URI = require('urijs')
import {
  Store
} from '@/framework/utils/util';

@Component({})
class QApp extends QScene {
  async created() {
    var self = this
    console.log('created', Util.isNative())

    if (Util.isNative()) {
      var url = process.env.URL_API;
      var data;
      var xhr = null;
      var prourl = null
    }

    function testXHR() {
      if (xhr) {
        return;
      }
      xhr = new plus.net.XMLHttpRequest();
      xhr.onreadystatechange = xhrStatechange;
      xhr.open("GET", url);
      xhr.send();
    }

    function xhrStatechange() {
      if (xhr.readyState == 4 && xhr.status == 200) {
        data = JSON.parse(xhr.responseText);
        if (data['domain']) {
          var prourl = data['domain']
          var prohot = data['hotupdata']
          var store = Store.getStore()
          store.set('hotupdateurl', {
            prourl: prourl,
            prohot: prohot
          })
          self.hotupdata()
        } else {
          Store.getStore().remove('hotupdateurl')
        }
      } else {
        Store.getStore().remove('hotupdateurl')
      }
    }
    if(process.env.NODE_ENV !== 'development'){
      testXHR()
    }
  }
  async hotupdata() {
    var self = this
    plus.runtime.getProperty(plus.runtime.appid, function(inf) {
      //获取当前版本号
      var version = inf.version;
      console.log(version, '当前前版本')
      var url = Store.getStore().get('hotupdateurl')
      var prohot = process.env.VER_API;
      if (url.prohot) {
        prohot = url.prohot
      }
      console.log(prohot)
      var xhr = null;

      function testXHR1() {
        xhr = new plus.net.XMLHttpRequest();
        xhr.onreadystatechange = xhrStatechange;
        xhr.open("GET", prohot);
        xhr.send();
      }

      function xhrStatechange() {
        console.log(xhr)
        if (xhr.readyState == 4 && xhr.status == 200) {
          var data = JSON.parse(xhr.responseText);
          console.log(data) //
          if (version != data.version) {
            console.log(data.version)
            self.downWgt('http://' + data.downpath)
          }
        }
      }
      testXHR1()
    })

  }
  async downWgt(downpath) {
    var self = this
    console.log(downpath)
    plus.nativeUI.showWaiting("下载热更新文件...");
    plus.downloader.createDownload(downpath, {
      filename: "_doc/update/"
    }, function(d, status) {
      if (status == 200) {
        console.log("下载热更新成功：" + d.filename);

        self.installWgt(d.filename); // 安装wgt包
      } else {
        console.log("下载热更新失败！");
        plus.nativeUI.alert("下载热更新失败！");
      }
      plus.nativeUI.closeWaiting();
    }).start();
  }
  async installWgt(path) {
    plus.nativeUI.showWaiting("安装wgt文件...");
    plus.runtime.install(path, {}, function(e) {
      // console.log(JSON.stringify(e))
      plus.nativeUI.closeWaiting();
      console.log("安装热更新文件成功！");
      plus.nativeUI.alert("应用资源更新完成！", function() {
        // plus.runtime.restart();
        plus.runtime.quit();
      });
    }, function(e) {
      plus.nativeUI.closeWaiting();
      console.log("安装热更新文件失败[" + e.code + "]：" + e.message);
      // plus.nativeUI.alert("安装wgt文件失败[" + e.code + "]：" + e.message);
    });
  }
}


export {
  QApp
}
