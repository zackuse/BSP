/**
 * http请求模块
 */
import axios from 'axios'
import Qs from 'qs'
import {
  Store
} from '@/framework/utils/util';
import {
  Util
} from '@/framework/utils/util'
const URI = require('urijs')
export async function requests(url, params, method) {
  var newurl = process.env.BASE_API
  var hoturl = Store.getStore().get('hotupdateurl')
  if (hoturl.prourl) {
    newurl = hoturl.prourl
  }
  var uri = new URI(url)
  newurl = newurl + uri.pathname()
  var data = {
    url: newurl,
    method: method,
    params: params
  }

  const conf = {
    headers: {
      // Authorization: "token"
    },
    ...data
  }

  var res = await axios(conf)
  return res
}

export async function get(url, params) {
  var newurl = process.env.BASE_API
  var hoturl = Store.getStore().get('hotupdateurl')
  // console.log(hoturl)

  if (hoturl != undefined) {
    if (hoturl.prourl) {
      newurl = hoturl.prourl
    }
  }

  var uri = new URI(url)

  newurl = newurl + uri.pathname()

  var data = {
    url: newurl,
    method: 'get',
    params: params
  }

  const conf = {
    headers: {
      // Authorization: "token"
    },
    ...data
  }

  var res = await axios(conf)
  return res
}

export async function post(url, params) {
  var newurl = process.env.BASE_API
  var hoturl = Store.getStore().get('hotupdateurl')
  if (hoturl != undefined) {
    if (hoturl.prourl) {
      newurl = hoturl.prourl
    }
  }
  var uri = new URI(url)

  newurl = newurl + uri.pathname()

  var data = {
    url: newurl,
    method: 'post',
    data: Qs.stringify(params)
  }

  const conf = {
    headers: {
      // 'content-type': 'multipart/form-data',
    },
    ...data
  }

  var res = await axios(conf)
  return res
}
