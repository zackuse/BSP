'use strict'
const merge = require('webpack-merge')
const prodEnv = require('./prod.env')

module.exports = merge(prodEnv, {
  NODE_ENV: '"development"',
  BASE_API: '"http://00881.com.cn"',
  VER_API: '"http://stphot.oss-cn-beijing.aliyuncs.com/hothbuilder.json"',
  URL_API: '"http://stphot.oss-cn-beijing.aliyuncs.com/domain.json"',
})
