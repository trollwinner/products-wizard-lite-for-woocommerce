function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/* WooCommerce Products Wizard Shared Editor Modal
 * Original author: Alex Troll
 * Further changes, comments: mail@troll-winner.com
 */

(function (root, factory) {
  'use strict';

  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else if ((typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object' && typeof module !== 'undefined' && typeof require === 'function') {
    module.exports = factory(require('jquery'));
  } else {
    factory(root.jQuery);
  }
})(this, function ($) {
  'use strict';

  var $document = $(document);
  var namespace = 'wcpw';

  // open shared wp-editor modal
  $document.on('click', "[data-component~=\"".concat(namespace, "-shared-editor-open\"]"), function (event) {
    event.preventDefault();
    var $element = $(this);
    var $target = $element.next("[data-component~=\"".concat(namespace, "-shared-editor-target\"]"));
    var $sharedEditorModal = $("#".concat(namespace, "-shared-editor-modal"));

    // for a modal in a modal
    if (window.location.hash && window.location.hash !== 'close') {
      $sharedEditorModal.find('[href]').each(function () {
        return $(this).attr('href', window.location.hash);
      });
    }
    $sharedEditorModal.addClass('is-opened').data('target', $target);

    // set editor content
    if ($("#wp-".concat(namespace, "-shared-editor-wrap")).hasClass('tmce-active') && window.tinyMCE.get("".concat(namespace, "-shared-editor"))) {
      window.tinyMCE.get("".concat(namespace, "-shared-editor")).setContent($target.val());
    } else {
      $("#".concat(namespace, "-shared-editor")).val($target.val());
    }
  });

  // modal save click
  $document.on('click', "#".concat(namespace, "-shared-editor-save"), function () {
    var $sharedEditorModal = $("#".concat(namespace, "-shared-editor-modal"));
    var content = $("#".concat(namespace, "-shared-editor")).val();

    // get editor content
    if ($("#wp-".concat(namespace, "-shared-editor-wrap")).hasClass('tmce-active') && window.tinyMCE.get("".concat(namespace, "-shared-editor"))) {
      content = window.tinyMCE.get("".concat(namespace, "-shared-editor")).getContent();
    }
    $sharedEditorModal.data('target').val(content);
  });
});