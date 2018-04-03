/**
 * tipfooter.js
 * Author: Adam Beagle
 *
 * Handles vote buttons and tip controls. 
 * Include on any page that contains tips.
 */
(function() {
  var upBtns = document.querySelectorAll('.vote-up .vote-btn');
  var  downBtns = document.querySelectorAll('.vote-down .vote-btn');

  function addVoteUp(btn) {
    var scoreEl = btn.parentElement.parentElement.querySelector('.score');

    api.voteTipUp(btn.dataset.id);
    btn.classList.add('voted');

    removeVoteDown(btn.parentElement.parentElement);
    scoreEl.innerHTML = parseInt(scoreEl.innerHTML) + 1;
  }

  function addVoteDown(btn) {
    var scoreEl = btn.parentElement.parentElement.querySelector('.score');

    removeVoteUp(btn.parentElement.parentElement);
    api.voteTipDown(btn.dataset.id);
    btn.classList.add('voted');

    scoreEl.innerHTML = parseInt(scoreEl.innerHTML) - 1;
  }

  function removeVoteUp(div) {
    var scoreEl = div.querySelector('.score'),
        btn = div.querySelector('.vote-up .vote-btn');

    if (btn.classList.contains('voted')) {
      api.cancelVote(btn.dataset.id, 'down');
      scoreEl.innerHTML = parseInt(scoreEl.innerHTML) - 1;
      btn.classList.remove('voted');
    }
  }
  
  function removeVoteDown(div) {
    var scoreEl = div.querySelector('.score'),
        btn = div.querySelector('.vote-down .vote-btn');

    if (btn.classList.contains('voted')) {
      api.cancelVote(btn.dataset.id, 'up');
      scoreEl.innerHTML = parseInt(scoreEl.innerHTML) + 1;
      btn.classList.remove('voted');
    }
  }

  // Attach upvote listener
  upBtns.forEach(function(btn) {
    btn.addEventListener('click', function(evt) {
      if (btn.classList.contains('voted')) {
        removeVoteUp(btn.parentElement.parentElement);
      }
      else {
        addVoteUp(btn);
      }
    });
  });

  // Attach downvote listener
  downBtns.forEach(function(btn) {
    btn.addEventListener('click', function(evt) {
      if (btn.classList.contains('voted')) {
        removeVoteDown(btn.parentElement.parentElement);
      }
      else {
        addVoteDown(btn);
      }
    });
  });
  
  // Confirmation message for tip delete
  $('.tip-control.delete').on('click', function(evt) {
    if (evt.which == 1) {
      if (!confirm('Are you sure you want to delete this tip?')) {
        evt.preventDefault();
      };
    }
  });
})();