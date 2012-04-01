if (typeof require !== "undefined") var _ = require('underscore');

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
  },

  /* Javascript's default String.prototype.split has a limit option, but it
   * doesn't actually limit the number of splits, only the number that are
   * returned.  This function actually only splits on the first n occurences
   * of the given delimiter
   */
  split: function(string, delimiter, limit) {
    var splits = string.split(delimiter)
      , tail   = splits.splice(limit).join(delimiter);
    splits.push(tail);
    return splits;
  }

};

if (typeof module !== "undefined") module.exports = TF2PICKUP_UTILITY;