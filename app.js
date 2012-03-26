// Module dependencies
var express      = require('express')
  , passport     = require('passport')
  , steam_login  = require('./steam.js')
  , steam_api    = require('steam');

// Load configuration
var config = require('./config.js');
console.log(config);
var url = 'http://' + config.host_name;

var app = express.createServer();

// Configuration
app.configure(function(){
  app.set('views', __dirname + '/views');
  app.set('view engine', 'ejs');
  app.use(express.bodyParser());
  app.use(express.methodOverride());
  app.use(express.cookieParser());
  app.use(express.session({ secret: 'tf2pickup949172463r57276' }));
  app.use(passport.initialize());
  app.use(passport.session());
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

// Routes

// Index page
app.get('/', function (req, res) {
  res.render('index', {
    logged_in: false,
    openid_url: steam_login.genURL(url + '/verify', url),
    avatar: 'http://media.steampowered.com/steamcommunity/public/images/avatars/36/36c4432f81708340cd76a40df82e0830c76b9e41.jpg',
    username: 'Lieutenant Awesome',
  });
});

// General API
app.post('/createPUG', function (req, res) {});
app.post('/getPUGs', function (req, res) {});

// Login stuff
app.get('/verify', steam_login.verify, function(req, res) {
  // Returned from authentication
  console.log('User logged in: ' + req.steam64);
  req.session.steam64 = req.steam64;
  res.redirect('/');
});

// Steam API proxies
app.get('/friends', function (req, res) {

});

app.listen(config.port);
console.log("Express server running tf2pickup on port %d in %s mode"
                , app.address().port, app.settings.env);
