/**
 * Node.js RCON implementation
 *
 * Derived from https://github.com/Nican/SRCDS-node.js-tools
 *
 * Protocol reference:
 * https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
 *
 * Modified by Gcommer to work with tf2pickup
 * (At this point, I've rewritten the vast majority of the code)
 */

var util = require('util');
var events = require("events");
var net = require("net");

var SERVERDATA_EXECCOMMAND = 2;
var SERVERDATA_AUTH = 3;

var SERVERDATA_RESPONSE_VALUE = 0;
var SERVERDATA_AUTH_RESPONSE = 2;

function RCon(host, port, callback) {
  var self = this;
	net.Socket.call(this);

	this.host = host;
	this.port = port;

	this.buffer          = null;
  this.bytes_received  = 0;
  this.bytes_remaining = 0;

  this.init_callback = callback;

  this.connected = false;
	this.connect(this.port, this.host);

  // Map of request ids -> callback functions
  this.requests = {};

  this.on('connect', function() {
    self.connected = true;
    self.init_callback.call(self, null);
  });

	this.on('data', this._receiveMessage );
	this.on('error', this._receiveError );
	this.on('close', this._receiveError );
}

util.inherits(RCon, net.Socket);

RCon.prototype._receiveError = function(data){
  if (!this.connected)
    this.init_callback("Could not connect to server");
};

RCon.prototype.generateRequestId = (function() {
  var n = 0;
  return function() { return n++; };
})();

RCon.prototype.auth = function(password, callback) {
  var self = this;
  var request_id = this._command(SERVERDATA_AUTH, password);

  this.auth_callback = function(id) {
		if (id === -1)
			callback.call(self, "Incorrect RCON password");
		else if (id === request_id)
			callback.call(self, null);
    else
      callback.call(self, "Invalid response to RCON authentication");
	};
};

RCon.prototype.send = function(cmd, callback) {
	this._command(SERVERDATA_EXECCOMMAND, cmd, callback);
};

RCon.prototype._command = function(cmd, message, callback) {
  var out_len    = 14 + message.length
    , buffer     = new Buffer(out_len)
    , request_id = this.generateRequestId();

  buffer.writeInt32LE(out_len - 4 , 0);
  buffer.writeInt32LE(request_id  , 4);
  buffer.writeInt32LE(cmd         , 8);
  buffer.write       (message     , 12, message.length, 'ascii');
  buffer.writeInt8   (0           , out_len - 2);
  buffer.writeInt8   (0           , out_len - 1);

	this.write(buffer);

  if (cmd === SERVERDATA_EXECCOMMAND)
    this.requests[request_id] = callback;

  return request_id;
};

RCon.prototype._receiveMessage = function(data) {
  // Messages come from the server in chunks, with the first int of the first
  // chunk specifying the number of bytes in the complete message
	if (this.buffer === null) {
    var total_bytes      = data.readInt32LE(0) + 4;
		this.buffer          = new Buffer(total_bytes);
    this.bytes_received  = 0;
    this.bytes_remaining = total_bytes; // exclude 'size' byte
	}

  var bytes_to_read = Math.min(data.length, this.bytes_remaining);

  data.copy(this.buffer, this.bytes_received, 0, bytes_to_read);

  this.bytes_received  += bytes_to_read;
  this.bytes_remaining -= bytes_to_read;

	if (this.bytes_remaining === 0) {
    // Done receiving, read packet data from this buffer:
    var request_id = this.buffer.readInt32LE(4);
		var response   = this.buffer.readInt32LE(8);

    // Operate under the assumption str2 is always empty
    var str1       = this.buffer.toString('ascii', 12, this.bytes_received - 2);
//  var str2       = buffer.readInt32LE(0);

    if (response === SERVERDATA_RESPONSE_VALUE
     && typeof this.requests[request_id] === "function") {
      this.requests[request_id](str1);
      delete this.requests[request_id];
    }
    else if (response === SERVERDATA_AUTH_RESPONSE)
      this.auth_callback(request_id);

 		this.buffer = null;
	}

  if (bytes_to_read !== data.length)
    this._receiveMessage(data.slice(bytes_to_read));
};

exports.RCon = RCon;