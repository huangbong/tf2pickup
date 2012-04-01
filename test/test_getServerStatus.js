var getServerStatus = require('../app.js').getServerStatus;

getServerStatus('70.42.74.154', 27015, 'swoleness', function(e, data) {
  if (e) console.log("ERROR: " + e);
  else   console.log(data);
});