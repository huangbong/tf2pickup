var utils  = require('../public/scripts/utility.js')
  , should = require('should');

describe('Utils', function() {
  describe('#split', function() {
    var str = "a b c d e";
    it('should do nothing when the limit is 0', function() {
      utils.split(str, " ", 0).should.eql([str]);
    });
    it('should return 2 elements when the limit is 1', function() {
      utils.split(str, " ", 1).should.eql(["a", "b c d e"]);
    });
  });
});