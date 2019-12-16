/* eslint-disable no-throw-literal */
const fs = require('fs')
const path = require('path')
const HtmlWebpackPlugin = require('html-webpack-plugin-for-multihtml')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const config = require('../config')
const walkSync = require('walk-sync')
const os = require('os')


// 通过页面配置文件过去页面json
var iswin = false
if (os.platform() === 'win32') {
  iswin = true
}


function generateByConfig () {
  var realPath = path.resolve('./src/page')
  var srcPath = path.resolve('./src')

  const paths = walkSync(realPath)

  var vueFiles = {}
  var enteryFiles = {}
  var codeFiles = {}
  var cssFiles = {}
  var lessFiles = {}

  for (var i = 0; i < paths.length; i++) {
    let p = path.join(realPath, paths[i])
    let stat = fs.lstatSync(p)
    if (stat.isFile()) {
      var relativePath = path.relative(srcPath, p)

      var splits = relativePath.split(path.sep)
      // console.log(splits)

      if (splits.length > 3) {
        throw 'page目录内不能有超过2级的目录'
      }

      var relPagePath = path.relative(realPath, p)
      var splits1 = relPagePath.split(path.sep)
      console.log(splits1)

      // let dirname = path.dirname(p)
      let dirname = path.dirname(p).split(path.sep).pop()
      console.log('dirname' + dirname)
      if (dirname !== 'components') {
        var fileName = null
        if (iswin) {
          fileName = path.win32.basename(p)
        // console.log(path.win32.dirname(p))
        } else {
          fileName = path.posix.basename(p)
        // console.log(path.posix.dirname(p))
        }

        console.log('ext=', path.extname(fileName))
        var ext = path.extname(p)
        if (ext === '.vue') {
          var vueName = path.basename(p, '.vue')
          vueFiles[vueName] = {dirname: dirname, ext: ext, path: p, srcPath: './' + path.join('./src/page/', splits1[0], splits1[1])}
        } else if (ext === '.css') {
          var cssName = path.basename(p, '.css')
          cssFiles[cssName] = {dirname: dirname, ext: ext, path: p, srcPath: './' + path.join('./src/page/', splits1[0], splits1[1])}
        } else if (ext === '.less') {
          var lessName = path.basename(p, '.less')
          lessFiles[lessName] = {dirname: dirname, ext: ext, path: p, srcPath: './' + path.join('./src/page/', splits1[0], splits1[1])}
        } else if (ext === '.js') {
          if (p.indexOf('.entry.js') !== -1) {
            var enteryFileName = path.basename(p, '.entry.js')
            enteryFiles[enteryFileName] = {dirname: dirname, ext: ext, path: p, srcPath: './' + path.join('./src/page/', splits1[0], splits1[1])}
          } else {
            var codeFileName = path.basename(p, '.js')
            codeFiles[codeFileName] = {dirname: dirname, ext: ext, path: p, srcPath: './' + path.join('./src/page/', splits1[0], splits1[1])}
          }
        }
      }
    }
  }
  var obj = {}
  obj['index'] = './src/app.entry.js'
  for (var key in enteryFiles) {
    if (!enteryFiles[key]) {
      throw '没有找到' + key + '.entry.js'
    }

    if (!codeFiles[key]) {
      throw '没有找到' + key + '.js'
    }

    if (!cssFiles[key] && !lessFiles[key]) {
      throw '没有找到' + key + '.css 或者' + key + '.less文件'
    }

    var dirname = enteryFiles[key].dirname
    obj[dirname + '.' + key] = enteryFiles[key].srcPath
  }

  return obj
}


// 生成extraEntry
const extraEntry = generateByConfig()

let newExtraEntry = {}

// 生成HtmlWebpackPlugin
let extraHtmlWebpackPlugins = []
let haveMui = 0
let useMui = false
let usePlusReady = true
for (let i in extraEntry) {
  // 配置是否使用mui plusready
  console.log('one entery:' + JSON.stringify(i))
  // const useMui = /\S+\|mui/.test(i)
  // const usePlusReady = /\S+\|plusReady/.test(i)
  // let chunk = useMui ? i.replace('|mui', '') : i
  let chunk = i
  // 如果用了mui就要导入mui资源
  // useMui && haveMui++
  // 提前加载plus
  // if (usePlusReady) chunk = chunk.replace('|plusReady', '')
  newExtraEntry[chunk] = extraEntry[i]
  extraHtmlWebpackPlugins.push(
    new HtmlWebpackPlugin({
      filename: chunk + '.html',
      template: 'index.html',
      multihtmlCache: true,
      chunks: [chunk, 'vue'],
      muiCssString: useMui ? '<link rel="stylesheet" href="assets/mui/mui.css">' : '',
      muiScriptString: useMui ? ' <script src="assets/mui/mui.min.js"></script>' : '',
      plusReady: usePlusReady ? '<script src="html5plus://ready"></script>' : ''
      // 获取mui的script
    })
  )
}
// 复制mui资源
if (haveMui) {
  extraHtmlWebpackPlugins.push(
    new CopyWebpackPlugin([
      {
        from: path.resolve(__dirname, '../src/assets/mui'),
        to: config.build.assetsSubDirectory + '/mui',
        ignore: ['.*']
      }
    ])
  )
}

exports.extraEntry = newExtraEntry
exports.extraHtmlWebpackPlugins = extraHtmlWebpackPlugins
