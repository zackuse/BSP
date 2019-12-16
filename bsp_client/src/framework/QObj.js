import Vue from 'vue'
import { TaskTimer, Task } from 'tasktimer'

class QObj extends Vue {
  constructor () {
    super()
    this.tickers = {}
  }

  _createTick (id, msec, params, count, tickInterval = 0, tickDelay = 0) {
    var self = this
    let t = new TaskTimer(msec)
    self.tickers[id] = t

    var options = {}
    options.id = id
    options.tickInterval = tickInterval
    options.tickDelay = tickDelay
    if (count !== null) {
      options.totalRuns = count
    }

    options.callback = async function (task) {
      console.log('on tick' + new Date().getTime())
      if (self.onTick) {
        t.pause()
        await self.onTick(id, params)
        t.resume()
      }
    }
    t.add(options)
    t.on(TaskTimer.Event.STOPPED, async () => {
      delete self.tickers[id]
      if (self.onTickStop) {
        await self.onTickStop(id)
      }
    })
    t.on(TaskTimer.Event.COMPLETED,async () => {
      delete self.tickers[id]
      if (self.onTickStop) {
        await self.onTickStop(id)
      }
    })
    t.start()
  }

  async tick (id, msec, params, count) {
    if (this.tickers[id]) {
      console.error('定时器已经存在:' + id)
    } else {
      this._createTick(id, msec, params, count)
    }
  }

  async after (id, msec, params) {
    if (this.tickers[id]) {
      console.error('定时器已经存在:' + id)
    } else {
      this._createTick(id, msec, params, 1)
    }
  }

  clear (id) {
    var self = this
    if (self.tickers[id]) {
      let t = self.tickers[id]
      t.stop()
    }
  }

  clearAll () {
    var self = this
    var entries = Object.entries(self.tickers)
    entries.forEach(element => {
      let t = element[1]
      t.stop()
    })
  }
}

export {
  QObj
}
