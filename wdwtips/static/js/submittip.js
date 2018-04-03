/**
 * submittip.js
 *
 * JS/jQuery for the tip submission page.
 */

new MaxlenWidget('.submit-tip input[name="title"]');
new MaxlenWidget('.create-account input[name="username"]');

// Topics
(function() { 
  function appendSubtopicSelect(subtopics, select) {
    var newSelect = $('<select class="topics"><option value="' + select.val() + '"></option></select>');

    subtopics.forEach(function(st) {
      $('<option/>', { 
        val: select.val() + '+' + st.slug,
        text: st.name[0]
      }).appendTo(newSelect);
    });

    if (subtopics.length > 0) {
      newSelect.change(updateSubtopicSelects);
      newSelect.appendTo(select.parent());

      select.removeAttr('name');
      newSelect.attr('name', 'topic');
    }
  }

  function updateSubtopicSelects() {
    var select = $(this);

    select.nextAll().remove();
    select.after(' ');

    // If clicked empty option, do nothing
    if (!$('option:selected', select).text()) {
      select.attr('name', 'topic');
      return;
    }

    $.get(api.ROOT + 'topics', {topic: select.val()})
      .done(function(data) {
        appendSubtopicSelect(data.subtopics, select)
      })
      .fail(function() {
        console.log('Request fail');
      });
  }

//  function checkTitleLength() {
//    var maxLen = 300,
//        disp = $('.title-len'),
//        inp,
//        len;
//
//    if (this === window) {
//      inp = document.querySelector('input[name="title"]');
//    }
//    else {
//      inp = this;
//    }
//
//    // Update length display
//    len = inp.value.length;
//    disp.html('(' + len + ' / ' + maxLen + ')');
//
//    // Toggle success/error classes as needed
//    // ('not-fine' if length exceeds max)
//    if (len > maxLen) {
//      disp.removeClass('fine');
//      disp.addClass('not-fine');
//    }
//    else {
//      disp.removeClass('not-fine');
//      disp.addClass('fine');
//    }
//  }
//  
  $('.submit-tip select.topics').change(updateSubtopicSelects);
//
//  // Only call if approptiate input on submit tip page exists
//  if ($('.submit-tip input[name="title"]').length) {
//    checkTitleLength();
//  }
//  $('.submit-tip input[name="title"]').on('input', checkTitleLength);
})();

// Tags
(function() {
  // Yes, should be const
  // gulp-uglify can't do ES6; gulp-uglify-es is throwing some error
  var MAX_LENGTH = 32,
      MAX_TAGS = 10;
  
  var validKeyPattern = /^[a-z0-9-]$/i,
      tags = new Set(),
      invalidKeyMsg = $('form.tip .invalid-key-msg'),
      tagInput = $('form.tip input.add-tag');
  
  var closeBtn = $('<button />', {
    'class': 'remove',
    html: '&#x2716;'
  });
  closeBtn.attr('aria-label', 'Remove tag');
  
  // Add tag <a> and add value to hidden tags input
  // Assumes tag already validated
  function addTag(tag) {
    var tagA = $('<a/>', {
      text: tag,
      'class': 'btn tag'
    });
    
    tagA.append(closeBtn.clone(true));
    tags.add(tag);
    
    invalidKeyMsg.fadeOut(400);
    $('form.tip .added-tags').append(tagA);
  }
  
  // Return tag lowercase and stripped of any
  // non-valid characters
  function normalizeTag(tag) {
    return tag.replace(/[^a-z0-9-]/gi, '').toLowerCase();
  }
  
  function removeTag(evt) {
    evt.preventDefault(); // No form submit
    $(this).parent().remove();
  }
  
  // Tag remove button click
  closeBtn.click(removeTag);
  $('form.tip button.remove').click(removeTag);
  
  // On keypress in add-tag input, only allow event
  // if key valid for tag (alphanumeric or -).
  // Else show notice.
  tagInput.keypress(function(evt) {
    if (evt.key == 'Enter') {
      evt.preventDefault();
      $('form.tip button.add-tag').click();
    }
    else if (!validKeyPattern.test(evt.key)) {
      evt.preventDefault();
      invalidKeyMsg.fadeIn(250);
    }
    else {
      //invalidKeyMsg.css('display', 'none');
      invalidKeyMsg.fadeOut(250);
    }
  });
  
  // Add tag button click
  // Validate and add tag
  $('form.tip button.add-tag').click(function(evt) {
    // Sanitize/normalize tag
    var tag = normalizeTag(tagInput.val());
        
    // Prevent button from submitting form
    // Apparently it will do that even without type="submit"
    evt.preventDefault();
    
    // Add tag if not empty, unique, and max not reached
    if (tag) {
      if ($('a.btn.tag').length == MAX_TAGS) {
        $('.max-tags').css('font-weight', 'bold');
      }
      else if (!tags.has(tag)) {
        addTag(normalizeTag(tag));
        tagInput.val('');
      }
    }
  });
  
  // On form submission, build tags value for hidden input
  $('form.tip button[name="submit"]').click(function(evt) {
    values = [];
    
    $('a.btn.tag').each(function(i, x) {
      // Push text of <a> itself, not including any children
      // To avoid including button text
      values.push(x.childNodes[0].nodeValue.trim());
    });
    $('input[name="tags"]').val(values.join('+'));
  });
  
  // If tip form, get all tags to populate autocomplete
  if ($('form.tip').length > 0) {
    $.get(api.ROOT + 'tags')
      .done(function(data) {
        // jQuery-UI autocomplete for tags
        $('input.add-tag').autocomplete({
          source: data.tags
        });
      })
      .fail(function() {
        console.log('Request fail');
      });
  }
})();