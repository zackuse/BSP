// 工具类
var store = require('store')
var expirePlugin = require('store/plugins/expire')
store.addPlugin(expirePlugin)

class Util {
  static isNative() {
    return window.plus != null
  }

  static isWeb() {
    return window.plus === null
  }

  static isios() {
    var u = navigator.userAgent;
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    return isiOS;
  }
}

class Store {
  static getStore() {
    return store
  }
}

//剪切板
class ClipBoard {
  static set(s) {
    if (Util.isNative()) {
      if (Util.isios()) {
        var UIPasteboard = plus.ios.importClass("UIPasteboard");
        var generalPasteboard = UIPasteboard.generalPasteboard();
        generalPasteboard.plusCallMethod({
          setValue: s,
          forPasteboardType: "public.utf8-plain-text"
        })
        generalPasteboard.plusCallMethod({
          valueForPasteboardType: "public.utf8-plain-text"
        })
        return true
      } else {
        var context = plus.android.importClass("android.content.Context");
        var main = plus.android.runtimeMainActivity();
        var clip = main.getSystemService(context.CLIPBOARD_SERVICE);
        plus.android.invoke(clip, "setText", s);
        return true
      }
    } else {
      var oInput = document.createElement('input');
      oInput.value = s;
      document.body.appendChild(oInput);
      
      var range = document.createRange();
      range.selectNode(oInput);
      window.getSelection().addRange(range)
      oInput.select(); // 选择对象
      document.execCommand("Copy"); // 执行浏览器复制命令
      oInput.style.display='none';
      // 移除选中的元素
      window.getSelection().removeAllRanges();
      return true
    }
  }

  static get() {
    if (Util.isNative()) {
      if (Util.isios()) {
        var UIPasteboard = plus.ios.importClass("UIPasteboard");
        var generalPasteboard = UIPasteboard.generalPasteboard();
        var _val = generalPasteboard.plusCallMethod({
          valueForPasteboardType: "public.utf8-plain-text"
        });
        return _val;
      } else {
        var context = plus.android.importClass("android.content.Context");
        var main = plus.android.runtimeMainActivity();
        var clip = main.getSystemService(context.CLIPBOARD_SERVICE);
        var _val = plus.android.invoke(clip, "getText");
        return _val;
      }
    } else {
      navigator.clipboard.readText()
        .then(text => {
          return text
        })
        .catch(err => {
          return false
        });
    }
  }
}

export {
  Util,
  Store,
  ClipBoard
}
