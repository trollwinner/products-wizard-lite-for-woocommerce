function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/* WooCommerce Products Wizard main event handlers
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
  var $body = $(document.body);
  function setQueryArg(args) {
    if (!window.history || !window.history.pushState) {
      return this;
    }
    var params = new URLSearchParams(window.location.search);
    for (var key in args) {
      if (args.hasOwnProperty(key)) {
        params.set(key, args[key]);
      }
    }
    var path = window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + params.toString();
    window.history.pushState({
      path: path
    }, '', path);
    return this;
  }
  function initScripts() {
    $body.trigger('wc-enhanced-select-init');
    $document.trigger('init.thumbnail.wcpw');
    $document.trigger('init.multiSelect.wcpw');
    $document.trigger('init.dataTable.wcpw');
  }

  // get step setting
  $document.on('get.settings.item.steps.wcpw', function () {
    return initScripts();
  });

  // data table item added
  $document.on('added.item.dataTable.wcpw', function () {
    return initScripts();
  });

  // expose settings value as data-attribute
  $document.on('change', '[data-component~="wcpw-setting"] :input[data-value]', function () {
    this.setAttribute('data-value', this.value);
  });

  // toggle settings group
  $document.on('click', '[data-component~="wcpw-settings-group-toggle"]', function (event) {
    event.preventDefault();
    var $element = $(this);
    var $groups = $element.closest('[data-component~="wcpw-settings-groups"]');
    var $content = $groups.find('[data-component~="wcpw-settings-group-content"]');
    var $toggle = $groups.find('[data-component~="wcpw-settings-group-toggle"]');
    var $selectedContent = $content.filter("[data-id=\"".concat($element.data('id'), "\"]"));
    var isClosed = $selectedContent.attr('aria-expanded') === 'false';
    if (!isClosed) {
      $element.add($selectedContent).attr('aria-expanded', 'false');
      return this;
    }
    $toggle.add($content).attr('aria-expanded', 'false');
    $element.add($selectedContent).attr('aria-expanded', 'true');

    // save state to URL parameter
    setQueryArg({
      activeGroup: $element.data('id')
    });
    return this;
  });

  // <editor-fold desc="Modal">
  // open modal via JS too to be sure everything is alright (some 3rd code makes it wrong)
  $document.on('click', '[data-component~="wcpw-open-modal"]', function (event) {
    event.preventDefault();
    document.querySelector(this.getAttribute('href')).classList.add('is-opened');
    if (history.pushState) {
      history.pushState(null, null, this.getAttribute('href'));
    } else {
      location.hash = this.getAttribute('href');
    }
  });

  // close modal
  $document.on('click', '[data-component~="wcpw-modal-close"]', function () {
    this.closest('[data-component~="wcpw-modal"]').classList.remove('is-opened');
    if (history.pushState) {
      history.pushState(null, null, '#');
    } else {
      location.hash = '#';
    }
  });
  // </editor-fold>

  // set clipboard click
  $document.on('click', '[data-component~="wcpw-set-clipboard"]', function (event) {
    var _this = this;
    event.preventDefault();
    var text = this.getAttribute('data-clipboard-value');
    var result = function () {
      if (window.clipboardData && window.clipboardData.setData) {
        window.clipboardData.setData('Text', text);
        return true;
      } else if (document.queryCommandSupported && document.queryCommandSupported('copy')) {
        var textarea = document.createElement('textarea');
        textarea.textContent = text;
        textarea.style.position = 'fixed';
        document.body.appendChild(textarea);
        textarea.select();
        try {
          return document.execCommand('copy');
        } catch (ex) {
          return false;
        } finally {
          document.body.removeChild(textarea);
        }
      }
      return false;
    }();
    this.setAttribute('data-clipboard-initial-class', this.getAttribute('class'));
    this.setAttribute('class', this.getAttribute(result ? 'data-clipboard-success-class' : 'data-clipboard-error-class'));
    setTimeout(function () {
      _this.setAttribute('class', _this.getAttribute('data-clipboard-initial-class'));
    }, 1000);
  });
});