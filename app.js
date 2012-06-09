// Module dependencies
var express      = require('express')
  , connect      = require('express/node_modules/connect')
  , socket_io    = require('socket.io')
  , _            = require('underscore')
  , async        = require('async')
  , steam_login  = require('./steam.js')
  , steam_api    = require('steam')
  // TODO: use these:
  , check        = require('validator').check
  , sanitize     = require('validator').sanitize
  , geoip        = require('geoip-lite-rm')
  , to_continent = require('./continents.js').to_continent
  , rcon         = require('./rcon.js')
  , utils        = require('./public/scripts/utility.js');

var sessionStore = new connect.middleware.session.MemoryStore()
var app = express.createServer()
var io  = socket_io.listen(app);

geoip.load('./data');

// Load configuration
var config = require('./config.' + app.settings.env + '.js');
var url    = 'http://' + config.host_name;

var steam = new steam_api({ apiKey: config.steam_api_key, format: 'json' });

var users = {}, pugs = {}, friends_cache = {}, players_cache = {};

var generateUID = (function() {
  var n = 0;
  return function() { return n++; };
})();

// Lookup ip -> region code (NA, EU, AU, ...)
// Does not need to be asynchronous because the geoip library and
// country -> continent map are stored in memory
function getRegion(ip) {
  var data = geoip.lookup(ip);
  return data? to_continent(data.country).toLowerCase() : null;
};

// caching wrapper around steam api calls, usable with promises
function getFriends(steamid, callback) {
  if (friends_cache[steamid])
    callback(null, friends_cache[steamid]);
  else
    steam.getFriendList({
      steamid: steamid,
      relationship: 'all',
      callback: function(err, data) {
        if (!err && data && data.friendslist && data.friendslist.friends) {
          data = data.friendslist.friends.map(function(friend) {
            return friend.steamid;
          });
          friends_cache[steamid] = data;
          callback(null, data);
        }
        else
          callback(err, data);
      }
    });
};

function getPlayerInfo(steamid, callback) {
  if (players_cache[steamid])
    callback(null, players_cache[steamid])
  else
    steam.getPlayerSummaries({
      steamids: [steamid],
      callback: function(err, data) {
        if (!err && data && data.response && data.response.players[0]) {
          var player = data.response.players[0];
          players_cache[steamid] = player;
          callback(null, player);
        }
        else
          callback(err, data);
      }
    });
};

// Takes a server's raw status (from an rcon 'status' command) and parses it
// into an object, or returns false if it was invalid
function parseServerStatus(raw_data) {
  var data = raw_data.split("\n\n");
  if (data.length !== 2) return callback("Invalid server response");

  var headers = {}
    , _headers = data[0].split("\n")
    , players = data[1].split("\n");

  _.each(_headers, function(header) {
    var parts = utils.split(header, ':', 1);
    headers[parts[0].trim()] = parts[1].trim();
  });

  // Remove header row from player list
  players.shift();

  players = _.map(players, function(player) {
    // TODO: What if a player has a " in their name?
    if (player[0] === '#') {
      var row = player.slice(10).split('"');
      return {
        name: row[0],
        steamid: row[1].trim().split(" ")[0]
      };
    }
  });

  return {
    headers: headers,
    players: _.compact(players) // remove empty rows
  };
};

function getServerStatus(ip, port, pass, callback) {
  new rcon.RCon(ip, port, function(e) {
    if (e) return callback(e);

    this.auth(pass, function(e) {
      var socket = this;
      if (e) return callback(e);

      this.send('status', function(data) {
        socket.end();

        var data = parseServerStatus(data);
        if (data)
          callback(null, data);
        else
          callback("Invalid status from game server");
      });
    });
  });
};

function createUser(sessionid, steamid, callback) {
  async.parallel([
    function(callback) { getFriends(steamid, callback); }
  , function(callback) { getPlayerInfo(steamid, callback); }
  ],function(err, results) {
    if (users[sessionid])
      users[sessionid].destroy();

    // Kill any users with the same steamid
    _.chain(users)
     .filter(function(u) { return u.steamid === steamid; })
     .each(function(u) { u.destroy(); });

    if (err)
      callback(err);
    else {
      var friends = results[0], player = results[1];
      users[sessionid] = {
        // Data the client will be able to see
        data: {
          steamid: steamid,
          name: player.personaname,
          avatar: player.avatar,
          friends: friends
        },

        socket: null,
        sessionid: sessionid,

        onConnection: function(socket) {
          // Kill old connections using this session id and then
          // map this session id to the new socket
          this.disconnect();
          this.socket = socket;
        },

        destroy: function() {
          this.disconnect();
          this.session.destroy();
          delete users[this.sessionid];
        },

        disconnect: function() {
          if (this.socket)
            this.socket.disconnect();
        }
      };

      callback();
    }
  });
};

function createPug(new_pug, callback) {
  var required_keys = ['name', 'type', 'map', 'ip', 'port', 'rcon'];

  // If new_pug is missing any of required_keys
  if(!_.all(required_keys, function(key) { return _.has(new_pug, key); }))
    return callback("Missing argument");

  // Now that we know all the required parameters are present, verify them
  // TODO: Replace all these if's with a JSON schema module
  if (new_pug.name.length > 150)
    return callback("PUG Name too long");

  if (new_pug.map.length > 150)
    return callback("Map name too long");

  new_pug.ip = utils.simplifyIP(new_pug.ip);
  if (!new_pug.ip)
    return callback("Invalid IP");

  new_pug.port = parseInt(new_pug.port, 10);
  if (_.isNaN(new_pug.port) || new_pug.port < 0 || new_pug.port > 65535)
    return callback("Invalid port number");

  new_pug.type = parseInt(new_pug.type, 10);
  if (new_pug.type !== 1 && new_pug.type !== 2)
    return callback("Invalid PUG type");

  if (_.any(pugs, function(pug) {
    return pug.ip === new_pug.ip && pug.port === new_pug.port;
  }))
    return callback("Server already being used for a pug");

  // Get info about the server
  async.parallel([
    function(callback) { getServerStatus(new_pug.ip, new_pug.port
                                       , new_pug.rcon, callback); }
  , function(callback) { callback(null, getRegion(new_pug.ip));   }
  ], function(err, results) {
    if (err) return callback(err);

    var server_data   = results[0]
      , server_region = results[1];

    if (!server_data.headers.hostname || !server_data.headers.players)
      return callback('Server returned insufficient information');

    var required_players = ((new_pug.type === 1)? 6:9) * 2;
    var max_players = server_data.headers.players.match(/\d+ \((\d+) max\)/);
    if (!max_players)
      return callback('Server returned an invalid status message');
    else if (required_players > parseInt(max_players[1], 10))
      return callback('Server does not have enough player slots');

    var non_bots = _.filter(server_data.players.length, function(player) {
      return player.steamid !== "BOT";
    });

    if (non_bots.length > 0)
      return callback("Server already has players on it.");

    // TODO: Check if server has the map

    var id = generateUID();
    pugs[id] = {
      id: id,
      server_name: server_data.headers.hostname,
      region:      server_region,
      players:     [],

      name: new_pug.name,
      type: new_pug.type,
      map:  new_pug.map,
      ip:   new_pug.ip,
      port: new_pug.port
    };

    callback(null, pugs[id]);
  });
};

function pugsRemovePlayer(steamid) {
  _.each(pugs, function(pug, pug_id) {
    var player = _.find(pug.players, function(p) {
      return p.steamid === steamid;
    });

    if (player) {
      io.sockets.emit('leave', { pug_id: pug_id, steamid: steamid });
      pug.players.splice(pug.players.indexOf(player), 1);
    }
  });
};

// Adds the given user as a player in the pug specified by pug_id
// Returns true on success, false on error
function pugAddPlayer(pug_id, user, class_id, team_id) {
  if (!pugs[pug_id]) return false;
  var players = pugs[pug_id].players
    , available_classes = utils.getClasses(pugs[pug_id].pug_type)
    , taken_classes;

  // get the class id's of all player's with the given team_id
  taken_classes = _.map(_.filter(players,
                                 function(p) { return p.team_id === team_id; }),
                        function(p) { return p.class_id; });

  available_classes = _.difference(available_classes, taken_classes);
  if (!_.find(available_classes, function(c) { return c === class_id }))
    return false; // Desired class not available

  var player = {
    team_id:  team_id,
    class_id: class_id,

    name:    user.data.name,
    avatar:  user.data.avatar,
    steamid: user.data.steamid
  };

  players.push(player);
  return player;
};

function loadUser(req, res, next) {
  req.user = users[req.session.id];
  next();
};

// Express Configuration
app.configure(function() {
  app.set('views', __dirname + '/views');
  app.set('view engine', 'ejs');
  app.use(express.cookieParser());
  app.use(express.session({ secret: 'tf2pickup949172463r57276'
                          , key: 'express.sid'
                          , store: sessionStore
                          , cookie: { maxAge: 5 * 24 * 60 * 60 * 1000 } }));
  app.use(app.router);
  app.use(express.static(__dirname + '/public'));
});

app.configure('development', function() {
  app.use(express.errorHandler({ dumpExceptions: true, showStack: true }));
//  app.use(express.logger());
});

app.configure('production', function() {
  app.use(express.errorHandler());
});

// Socket.IO Setup
io.set('log level', 1);
io.set('authorization', function (data, accept) {
  // This hoook binds express's session data to the socket.io connection
  if (!data.headers.cookie)
    return accept('No cookie transmitted.', false);

  data.cookie = connect.utils.parseCookie(data.headers.cookie);
  data.sessionID = data.cookie['express.sid'];

  sessionStore.load(data.sessionID, function (err, session) {
    if (err || !session)
      return accept('Error', false);

    if (!session.steamid || !users[session.id])
      return accept('Not authed', false);

    data.session = session;
    return accept(null, true);
  });
});

io.sockets.on('connection', function(socket) {
  var session = socket.handshake.session
    , user = users[session.id];

  console.log(user.data.name + " connected");
  user.onConnection(socket);

  socket.on('create pug', function(data) {
    createPug(data, function(err, new_pug) {
      if (!err) {
        io.sockets.emit('pug', [new_pug]);
        socket.emit('pug created', new_pug.id);
      }
      else
        socket.emit('create pug error', err);
    })
  });

  socket.on('join', function(pug_id, class_id, team_id) {
    // TODO: reload session?
    if (pugs[pug_id]                       // Valid pug_ud
    && 1 <= class_id && class_id <= 9      // Valid class_id
    && (team_id === 0 || team_id === 1)) { // Valid team_id
      var player;

      pugsRemovePlayer(user.data.steamid);
      player = pugAddPlayer(pug_id, user, class_id, team_id);

      if (player)
        io.sockets.emit('join', { pug_id: pug_id, player: player });
    }

  });

  socket.on('region', function(ip) {
    if ((ip = utils.simplifyIP(ip)))
      socket.emit('region', { ip: ip, region: getRegion(ip) || false });
  });

  socket.on('disconnect', function() {
    pugsRemovePlayer(user.data.steamid);
  });

  socket.emit('pug', pugs);
});

// Index page
app.get('/', loadUser, function (req, res) {
  res.render('index', {
    user: req.user? req.user.data : {},
    logged_in: req.user !== undefined,
    openid_url: steam_login.genURL(url + '/verify', url)
  });
});

app.get('/fake', function(req, res) {
  req.steamid = '76561197993836391';
  createUser(req.session.id, req.steamid, function(err) {
    if (!err) {
      console.log('User logged in: ' + req.steamid);
      req.session.steamid = req.steamid;
    }

    res.redirect('/');
  });
});

// Login via Steam
app.get('/verify', steam_login.verify, function(req, res) {
  // Returned from authentication
  createUser(req.session.id, req.steamid, function(err) {
    if (!err) {
      console.log('User logged in: ' + req.steamid);
      req.session.steamid = req.steamid;
    }

    res.redirect('/');
  });
});

app.listen(config.port);
console.log("Express server running tf2pickup on port %d in %s mode"
                , app.address().port, app.settings.env);

// For tests:
module.exports = {
  getServerStatus: getServerStatus
};