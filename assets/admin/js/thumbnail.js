function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/* WooCommerce Products Wizard Thumbnail
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

  var pluginName = 'wcpwThumbnail';
  var namespace = 'wcpw';
  var defaults = {};
  var $document = $(document);
  var Plugin = function Plugin(element, options) {
    this.element = element;
    this.options = $.extend({}, defaults, options);
    return this.init();
  };

  /**
   * Init the instance
   * @returns {this} self instance
   */
  Plugin.prototype.init = function () {
    this.$element = $(this.element);
    this.$id = this.$element.find("[data-component~=\"".concat(namespace, "-thumbnail-id\"]"));
    this.$image = this.$element.find("[data-component~=\"".concat(namespace, "-thumbnail-image\"]"));
    return this.initEventListeners();
  };

  /**
   * Add required event listeners
   * @returns {this} self instance
   */
  Plugin.prototype.initEventListeners = function () {
    var _this = this;
    // set thumbnail
    this.$element.on('click', "[data-component~=\"".concat(namespace, "-thumbnail-set\"]"), function (event) {
      event.preventDefault();
      return _this.openModal();
    });

    // remove thumbnail
    this.$element.on('click', "[data-component~=\"".concat(namespace, "-thumbnail-remove\"]"), function (event) {
      event.preventDefault();
      return _this.removeImage();
    });
    return this;
  };

  /**
   * Open thumbnail modal
   * @returns {this} self instance
   */
  Plugin.prototype.openModal = function () {
    var _this2 = this;
    // If the media frame already exists, reopen it.
    if (this.modalFrame) {
      this.modalFrame.open();
      return this;
    }

    // Create the media frame.
    this.modalFrame = wp.media.frames.downloadable_file = wp.media({
      title: 'Select image',
      button: {
        text: 'Select'
      },
      multiple: false
    });

    // When an image is selected, run a callback.
    this.modalFrame.on('select', function () {
      return _this2.modalFrame.state().get('selection').map(function (attachment) {
        var attachmentJson = attachment.toJSON();
        if (!attachmentJson.id) {
          return null;
        }
        var src = {}.hasOwnProperty.call(attachmentJson, 'sizes') && {}.hasOwnProperty.call(attachmentJson.sizes, 'thumbnail') ? attachmentJson.sizes.thumbnail.url : attachmentJson.url;
        _this2.$image.html("<img src=\"".concat(src, "\">"));
        _this2.$id.val(attachmentJson.id);
        _this2.$element.trigger("selected.thumbnail.".concat(namespace), [_this2, attachment]);
        return attachment;
      });
    });

    // Finally, open the modal
    return this.modalFrame.open();
  };

  /**
   * Detach image is and remove image
   * @returns {this} self instance
   */
  Plugin.prototype.removeImage = function () {
    this.$image.html('');
    this.$id.val('');
    this.$element.trigger("removed.thumbnail.".concat(namespace), [this]);
    return this;
  };
  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new Plugin(this, options));
      }
    });
  };
  var init = function init() {
    return $("[data-component~=\"".concat(namespace, "-thumbnail\"]")).each(function () {
      return $(this).wcpwThumbnail();
    });
  };
  $document.ready(function () {
    return init();
  });
  $document.on("init.thumbnail.".concat(namespace), function () {
    return init();
  });
});