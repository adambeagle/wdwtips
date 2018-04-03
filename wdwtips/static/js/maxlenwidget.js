/**
 * maxlenwidget.js
 * Author: Adam Beagle
 *
 * DESCRIPTION
 * ==================
 *
 * jQuery widget which places a visual indication, e.g. '(0 / xx)', 
 * displaying the current length of an input's text relative to a 
 * specified maximum.
 *
 * Auto-updates on any input. 
 * 
 * BASIC USAGE
 * ==================
 *
 * HTML:
 * ----------------
 *   <input type="text" name="xyz" maxlength="24">
 *   <span id="maxlen-widget"></span>
 *
 * JS:
 * ----------------
 *   <script>
 *     new MaxLenWidget('input[name="xyz"]');
 *   </script>
 *
 * CSS:
 * ----------------
 *   #maxlen-widget.length-valid {
 *     color: green;
 *     ...
 *   }
 *
 *   #maxlen-widget.length-invalid {
 *     color: red;
 *     ...
 *   }
 *
 * USAGE
 * ==================
 * 
 * Constructor
 * ----------------
 *   new MaxLenWidget(inputSelector, maxLength, 
 *                    validClass, invalidClass, widgetSelector)
 *
 *   Required:
 *     inputSelector  - CSS selector which points to the input
 *                      the widget describes.
 *
 *   Optional:
 *     maxLength      - Maximum length of input text. 
 *                      Must be set if input has no maxlength attribute.
 *                      Default: maxlength attribute of input
 *
 *     validClass     - CSS class to assign to widget when length is valid.
 *                      Default: 'length-valid'
 *
 *     invalidClass   - CSS class to assign to widget when length is invalid.
 *                      Default: 'length-invalid'
 *
 *     widgetSelector - Selector which points to the widget. This is useful
 *                      if you need two widgets on one page.
 *                      Default: '#maxlen-widget'
 */

var MaxlenWidget = function(inputSelector, maxLength, 
                            validClass, invalidClass, widgetSelector) {
  var inputEl,
      len,
      displayEl;
  
  // Log error message if required argument not sent
  if (inputSelector === undefined) {
    console.log('ERROR: no inputSelector provided to MaxlenWidget');
    return;
  }
  
  // Set default values for undefined optional args  
  if (validClass === undefined) {
    validClass = 'length-valid';
  }
  
  if (invalidClass === undefined) {
    invalidClass = 'length-invalid';
  }
  
  if (widgetSelector === undefined) {
    widgetSelector = '#maxlen-widget'
  }
  
  inputEl = $(inputSelector);
  displayEl = $(widgetSelector);
  
  if (maxLength === undefined) {
    maxLength = inputEl.attr('maxlength');
  }
  
  if (!inputEl.length) {
    return;
  }
  
  function update() {
    // Update length display
    len = inputEl.val().length;
    displayEl.html('(' + len + ' / ' + maxLength + ')');

    // Toggle classes as needed
    // (invalid if length exceeds max)
    if (len > maxLength) {
      displayEl.removeClass(validClass);
      displayEl.addClass(invalidClass);
    }
    else {
      displayEl.removeClass(invalidClass);
      displayEl.addClass(validClass);
    }
  }
  
  update();
  inputEl.on('input', update);
};