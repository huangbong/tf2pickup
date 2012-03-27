// Module dependencies
var express      = require('express')
  , connect      = require('express/node_modeles/connect')
  , socket_io    = require('socket.io')
  , _            = require('underscore')
  , steam_login  = require('./steam.js')
  , steam_api    = require('steam');

var sessionStore = new connect.middleware.session.MemoryStore();

// Load configuration
var config, url;
function loadConfig(filename) {
  config = require('./config.js');
  url = 'http://' + config.host_name;
}

var steam = new steam_api({ apiKey: config.steam_api_key, format: 'json' });

var app = express.createServer();
var io  = socket_io.listen(app);

// Express Configuration
app.configure(function(){
  app.set('views', __dirname + '/views');
  app.set('view engine', 'ejs');
  app.use(express.bodyParser());
  app.use(express.methodOverride());
  app.use(express.cookieParser());
  app.use(express.session({ secret: 'tf2pickup949172463r57276'
                          , cookie: 5 * 24 * 60 * 60 * 1000 }));
  app.use(app.router);
  app.use(express.static(__dirname + '/public'));
});

app.configure('development', function(){
  loadConfig('./config.dev.js');
  app.use(express.errorHandler({ dumpExceptions: true, showStack: true }));
//  app.use(express.logger());
});

app.configure('production', function(){
  loadConfig('./config.production.js');
  app.use(express.errorHandler());
});

// Socket.IO Setup
io.set('authorization', function (data, accept) {
  if (!data.headers.cookie)
    return accept('No cookie transmitted.', false);

  data.cookie = parseCookie(data.headers.cookie);
  data.sessionID = data.cookie['express.sid'];

  store.load(data.sessionID, function (err, session) {
    if (err || !session) return accept('Error', false);

    data.session = session;
    return accept(null, true);
  });
});

io.sockets.on('connection', function(socket) {
    socket.handshake.session;
  socket.on('get friends', function() {
    steam.getFriendList({
      steamid: socket.session.steamid,
      relationship: 'all',
      callback: function(err, data) {
        if (data) {
          data = data['friendslist']['friends'];
          data = data.map(function(friend) {return friend['steamid'];});
          socket.emit('friends', data);
        }
      }
    });
  });


});

// Index page
app.get('/', function (req, res) {
  res.render('index', {
    logged_in: req.session.steamid !== undefined,
    openid_url: steam_login.genURL(url + '/verify', url),
    avatar: config.steam_avatar_base_url
  + 'dbcfdc5d1e2dd48787a47b873874b3ca55f075ff.jpg',
    username: 'Lieutenant Awesome',
  });
});

// General API
app.post('/createPUG', function (req, res) {});
app.post('/getPUGs', function (req, res) {});

// Login via Steam
app.get('/verify', steam_login.verify, function(req, res) {
  // Returned from authentication
  console.log('User logged in: ' + req.steamid);
  req.session.steamid = req.steamid;
  res.redirect('/');
});

// Steam API proxies
app.get('/friends', function (req, res) {

});

app.listen(config.port);
console.log("Express server running tf2pickup on port %d in %s mode"
                , app.address().port, app.settings.env);
