if (require) var _ = require('underscore');

var TF2PICKUP_UTILITY = {

  /* Validates an IP address given by the user and normalizes it to its
   * minimum possible representation (001.022.3.4 -> 1.2.3.4)
   * Returns false if the given IP is invalid
   */
  simplifyIP: function(rawIP) {
    var octets = rawIP.split("."), invalid = false;
    if (octets.length !== 4) return false;

    octets = _.map(octets, function(octet) {
      octet = parseInt(octet, 10);
      if (_.isNaN(octet) || octet < 0 || octet > 255) invalid = true;
      return octet;
    });

    if (invalid)
      return false;
    else
      return octets.join(".");
  }

};

if (module) module.exports = TF2PICKUP_UTILITY;