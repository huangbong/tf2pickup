/**
 * Node.js RCON implementation
 *
 * Derived from https://github.com/Nican/SRCDS-node.js-tools
 *
 * Modified by Gcommer to work with tf2pickup
 */

var util = require('util');
var events = require("events");
var net = require("net");

exports.SERVERDATA_EXECCOMMAND = 2;
exports.SERVERDATA_AUTH = 3;

function RCon(host, port, callback) {
  var self = this;
	net.Socket.call(this);

	this.host = host;
	this.port = port;
	this.id = 0;

	this.buffer          = null;
  this.bytes_received  = 0;
  this.bytes_remaining = 0;

  this.callback = callback;

  this.connected = false;
	this.connect(this.port, this.host);

  this.on('connect', function() {
    self.connected = true;
    self.callback.call(self, null);
  });

	this.on('data', this._receiveMessage );
	this.on('error', this._receiveError );
	this.on('close', this._receiveError );
}

util.inherits(RCon, net.Socket);

RCon.prototype.auth = function(password, callback) {
  var self = this;
  this.command(exports.SERVERDATA_AUTH, password);

  this.once('auth', function(id) {
		if (id == -1)
			callback.call(self, "Could not auth with the server");
		else
			callback.call(self, null);
	});
};

RCon.prototype.send = function(cmd, callback) {
	var returnId = this.command(exports.SERVERDATA_EXECCOMMAND, cmd);

  this.on('response', function(id, type, data) {
    if (id == returnId) callback(data);
  });
};

RCon.prototype.command = function(cmd, message) {
  var out_len = 14 + message.length
    , buffer  = new Buffer(out_len);

  var request_id = Math.floor(Math.random() * 999999);

  buffer.writeInt32LE(out_len - 4 , 0);
  buffer.writeInt32LE(request_id  , 4);
  buffer.writeInt32LE(cmd         , 8);
  buffer.write       (message     , 12, message.length, 'ascii');
  buffer.writeInt8   (0           , out_len - 2);
  buffer.writeInt8   (0           , out_len - 1);

	this.write(buffer);

  return request_id;
};

RCon.prototype._receiveMessage = function(data) {
  var start = 0;

  // Messages come from the server in chunks, with the first int of the first
  // chunk specifying the number of bytes in the complete message
	if (this.buffer === null) {
    var total_bytes = data.readInt32LE(0);

		this.buffer   = new Buffer(total_bytes);

    this.bytes_received  = 0;
    this.bytes_remaining = total_bytes; // exclude 'size' byte

    start = 4;
	}

  var bytes_to_read = Math.min((data.length - start), this.bytes_remaining);

  data.copy(this.buffer, this.bytes_received, start, start + bytes_to_read);

  this.bytes_received  += bytes_to_read;
  this.bytes_remaining -= bytes_to_read;

	if (this.bytes_remaining === 0) {
    // Done receiving, read data from this packet
    var request_id = this.buffer.readInt32LE(0);
		var response   = this.buffer.readInt32LE(4);

    // Operate under the assumption str2 is always empty
    var str1       = this.buffer.toString('ascii', 8, this.buffer.length - 2);
//  var str2       = buffer.readInt32LE(0);

		this.buffer = null;
		this.emit('response', request_id, response, str1);

		if (response == 2)
			this.emit('auth', request_id);
	}

  if ((bytes_to_read + start) !== data.length)
    this._receiveMessage(data.slice(bytes_to_read));

};

RCon.prototype._receiveError = function(data){
  if (!this.connected) this.callback("Could not connect to server");
};

exports.RCon = RCon;