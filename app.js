// Module dependencies
var express      = require('express')
  , socket_io    = require('socket.io')
  , _            = require('underscore')
  , steam_login  = require('./steam.js')
  , steam_api    = require('steam');

// Load configuration
var config = require('./config.js');
var url = 'http://' + config.host_name;

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
  app.use(express.session({ secret: 'tf2pickup949172463r57276' }));
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
io.sockets.on('connection',

// Index page
app.get('/', function (req, res) {
  res.render('index', {
    logged_in: false,
    openid_url: steam_login.genURL(url + '/verify', url),
    avatar: config.steam_avatar_base_url
  + '36c4432f81708340cd76a40df82e0830c76b9e41.jpg',
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
  steam.getFriendList({
    steamid: req.session.steamid,
    relationship: 'all',
    callback: function(err, data) {
      if (data) {
        data = data['friendslist']['friends'];
        data = data.map(function(friend) {return friend['steamid'];});
	res.write(JSON.stringify(data));
      }
      res.end();
    }
  });
});

app.listen(config.port);
console.log("Express server running tf2pickup on port %d in %s mode"
                , app.address().port, app.settings.env);
