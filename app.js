// Module dependencies
var express      = require('express')
  , connect      = require('express/node_modules/connect')
  , socket_io    = require('socket.io')
  , _            = require('underscore')
  , async        = require('async')
  , steam_login  = require('./steam.js')
  , steam_api    = require('steam')

var sessionStore = new connect.middleware.session.MemoryStore()
var app = express.createServer()
var io  = socket_io.listen(app);

// Load configuration
var config = require('./config.' + app.settings.env + '.js');
var url    = 'http://' + config.host_name;

var steam = new steam_api({ apiKey: config.steam_api_key, format: 'json' });

var users = {}, pugs = {}, friends_cache = {}, players_cache = {};

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
          data = data.friendslist.friends.map(function(friend) {return friend.steamid;});
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
        if (!err && data && data.response && data.response.players[0])
          callback(null, data.response.players[0]);
        else
          callback(err, data);
      }
    });
};

function createUser(sessionid, steamid, callback) {
  if (users.hasOwnProperty(sessionid)) {
    users[sessionid].destroy();
    users[sessionid] = null;
  }

  async.parallel([
    function(callback) { getFriends(steamid, callback); }
  , function(callback) { getPlayerInfo(steamid, callback); }
  ],function(err, results) {
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

        onConnection: function(socket) {
          if (this.socket)
            this.socket.disconnect();
          this.socket = socket;
        },

        destroy: function() {
          this.socket.disconnect();
        }
      };
      callback();
    }
  });
};

function loadUser(req, res, next) {
  req.user = users[req.session.id];
  next();
};

// Express Configuration
app.configure(function(){
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

app.configure('development', function(){
  app.use(express.errorHandler({ dumpExceptions: true, showStack: true }));
//  app.use(express.logger());
});

app.configure('production', function(){
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


// Test code:
var n=0;
setInterval(function() {
  _.each(users, function(k, user) {

  });
  ++n;
}, 2000);

io.sockets.on('connection', function(socket) {
  var session = socket.handshake.session
    , user = users[session.id];

  user.onConnection(socket);
});

// Index page
app.get('/', loadUser, function (req, res) {
  res.render('index', {
    user: req.user? req.user.data : {},
    logged_in: req.user !== undefined,
    openid_url: steam_login.genURL(url + '/verify', url)
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
