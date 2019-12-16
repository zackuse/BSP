import {
	request
} from "@/utils/request";


class websocket {
	constructor() {
		this.url = null;
	}

	init(url, handler) {
		// TODO:检查是否以ws未开始，没有的话给加上
		// url = "ws://"+url
		this.url = url;
		this.ws = null;
		this.handler = handler;
	}
	
	send(data){
		if(this.readyState()=="OPEN"){
			var s = JSON.stringify(data)
			console.log("<-----------------------", s)
			this.ws.send(s)
		}
	}

	connect() {
		console.error(this.url)
		var ws = new WebSocket(this.url);
		ws.binaryType = "blob";


		var self = this;

		ws.onopen = function() {			
			// alert(s)
			var f = self.handler['wsonopen'];

			if (f) {
				self.handler.wsonopen()
			}

		};
		ws.onmessage = function(e) {
			if (e.data instanceof Blob) {
				var reader = new FileReader();
				reader.onload = function(event) {
					var content = reader.result; //内容就在这里
					var f = self.handler['wsonmessage'];
					if (f) {
						self.handler.wsonmessage(JSON.parse(content));
					}
				};
				reader.readAsText(e.data);
			}
		}

		ws.onclose = function(ev) {
			self.ws = null;
			var f = self.handler['wsonclose'];
			if (f) {
				self.handler.wsonclose()
			}
		};
		ws.onerror = function(ev) {
			self.ws = null;
			var f = self.handler['wsonerror'];
			if (f) {
				self.handler.wsonerror();
			}
		};

		this.ws = ws;
	}

	close() {
		if (this.readyState() == "OPEN") {
			this.ws.close();
		}
	}

	readyState() {
		if (this.ws == null) {
			return "CLOSED";
		}

		switch (this.ws.readyState) {
			case 0:
				return "CONNECTING";
			case 1:
				return "OPEN";
			case 2:
				return "CLOSING";
			case 3:
				return "CLOSED";
		}
	}


}

export {
	websocket
};
