var api = (function() {
  var ROOT;
  
  if (window.location.hostname.startsWith('pbcs.us')) {
    ROOT = '/~abeagle/projects/wdwtips/api/';
  }
  else {
    ROOT = '/projects/capstone/wdwtips/api/';
  }
  
  function cancelVote(id, direction) {
    var params = {
      tip: id,
      cancel: 1
    };
    params[direction] = 1;
    
    $.get(ROOT + 'vote', params)
      .done(function() {
        console.log('Request success');
      })
      .fail(function() {
        console.log('Request fail');
      }
    );
  }
  
  function voteTipUp(id) {
    $.get(ROOT + 'vote', {tip: id, up: 1})
      .done(function() {
        console.log('Request success');
      })
      .fail(function() {
        console.log('Request fail');
      }
    );
  }
  
  function voteTipDown(id) {
    $.get(ROOT + 'vote', {tip: id, down: 1})
      .done(function() {
        console.log('Request success');
      })
      .fail(function() {
        console.log('Request fail');
      }
    );
  }
  
  return {
    cancelVote: cancelVote,
    voteTipUp: voteTipUp,
    voteTipDown: voteTipDown,
    ROOT: ROOT
  }
})();
