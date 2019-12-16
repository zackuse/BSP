const path = require("path")
const SingleEntryPlugin = require("webpack/lib/SingleEntryPlugin")
class WatchPlugin {
    constructor(){
        console.log("Watch Plugin Contruct")
        // var options = {
        //   persistent: true,

        //   ignored: '*.txt',
        //   ignoreInitial: true,
        //   disableGlobbing: false,

        //   usePolling: true,
        //   interval: 100,
        //   binaryInterval: 300,
        //   alwaysStat: false,
        //   depth: 99,
        //   awaitWriteFinish: {
        //     stabilityThreshold: 2000,
        //     pollInterval: 100
        //  }
        // }

        // var self = this
        // let watcher = require('chokidar').watch(path.resolve('./src/page'),options)
        // watcher.on('add', f =>{
        //     if(self.compilation!=null){
        //         console.log('new file  ' + f)
        //         console.log('this context=' + self.context)
        //         self.compilation.addEntry(self.context, SingleEntryPlugin.createDependency(self.context, './src/page/login/register.entry.js'), "login.register", function (err){
        //             if (err) {
        //                 console.log(err);
        //             }
        //         });
        //     }
            
        // })
        // watcher.on('unlink', f =>{
        //     if(self.compilation!=null){
        //         console.log('delete file  ' + f)
        //     }
            
        // })
        // watcher.on('addDir', p =>{
        //     if(self.compilation!=null){
        //         console.log('new path  ' + p)
        //     }
            
        // })
        // watcher.on('unlinkDir', p =>{
        //     if(self.compilation!=null){
        //         console.log('new path  ' + p)
        //     }
            
        // })

        // this.compilation = null
        // this.context = null

        this.fileName = null
    }
    apply(compiler) {
        var self = this
        var realPath = path.resolve('./src/page/login')

        compiler.plugin('invalid', function (fileName, changeTime) {
                // changeFileName = fileName
                // console.log("fileName" + fileName)
                self.fileName = fileName
                console.log("self.filename =", self.fileName)
        });

        compiler.plugin("after-compile", function (compilation, callback) {
            // compilation.contextDependencies.add(globBasedir);
            // if(self.compilation==null){
            //     self.compilation=compilation
            //     self.context = this.context
            // }
            // console.log(realPath)
            compilation.contextDependencies.push(realPath);
            // console.log("after-compile")

            callback();
        });


        compiler.plugin("make", (compilation, callback) => {
            // console.log("make sss")
            // if(self.compilation==null){
            //     self.compilation=compilation
            //     self.context = this.context
            // }
            console.log("make" + self.fileName)
            if(self.fileName){
                console.log(path.posix.basename(self.fileName))
                if(path.posix.basename(self.fileName)==='register.entry.js') {
                    console.log("add entery",self.fileName)
                    compilation.addEntry(this.context, SingleEntryPlugin.createDependency("./src/page/login/register.entry.js", 'login.register'), 'login.register', callback);
                }else{
                    callback()
                }
            }else{
                callback()
            }
            // callback()
        })

        compiler.plugin('watch-run', function (compilation, callback) {
            // console.log("watch-run")
            // console.log(compilation.compiler.watchFileSystem.watcher.mtimes );
            // compilation.contextDependencies.add(globBasedir);
            // if(self.compilation==null){
            //     self.compilation=compilation
            //     self.context = this.context
            // }
            callback();
        })
    }

}



module.exports = WatchPlugin;