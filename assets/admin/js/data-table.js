function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/* WooCommerce Products Wizard Data Table
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

  var pluginName = 'wcpwDataTable';
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
    var _this2 = this;
    this.$element = $(this.element);
    this.hash = this.element.getAttribute('data-hash');
    this.$body = this.$element.children('table').children('tbody');
    this.$template = $('<div/>').html(this.$element.children("[data-component~=\"".concat(namespace, "-data-table-template\"]")).html()).contents();
    if ($.fn.sortable) {
      // init sortable
      this.$element.sortable({
        items: "[data-component~=\"".concat(namespace, "-data-table-item\"]"),
        distance: 50,
        update: function update() {
          return _this2.recalculateIds();
        },
        cancel: "input,textarea,button,select,option,.".concat(namespace, "-modal[data-hash=\"").concat(this.hash, "\"]")
      });
    }
    return this.initEventListeners().exposeCurrentValues();
  };

  /**
   * Add required event listeners
   * @returns {this} self instance
   */
  Plugin.prototype.initEventListeners = function () {
    var _this = this;

    // add the form item
    this.$element.on('click', "[data-component~=\"".concat(namespace, "-data-table-item-add\"][data-hash=\"").concat(this.hash, "\"]"), function () {
      return _this.addItem($(this).closest("[data-component~=\"".concat(namespace, "-data-table-item\"]")));
    });

    // remove the form item
    this.$element.on('click', "[data-component~=\"".concat(namespace, "-data-table-item-remove\"][data-hash=\"").concat(this.hash, "\"]"), function () {
      return _this.removeItem($(this).closest("[data-component~=\"".concat(namespace, "-data-table-item\"]")));
    });

    // clone the form item
    this.$element.on('click', "[data-component~=\"".concat(namespace, "-data-table-item-clone\"][data-hash=\"").concat(this.hash, "\"]"), function () {
      return _this.cloneItem($(this).closest("[data-component~=\"".concat(namespace, "-data-table-item\"]")));
    });

    // modal close click
    this.$element.on('click', "[data-component~=\"".concat(namespace, "-data-table-item-modal-close\"][data-hash=\"").concat(this.hash, "\"]"), function () {
      return _this.exposeCurrentValues(this.closest("[data-component~=\"".concat(namespace, "-data-table-item-modal\"]")));
    });

    // modal next/prev click
    this.$element.on('click', "[data-component~=\"".concat(namespace, "-data-table-item-modal-switch\"][data-hash=\"").concat(this.hash, "\"]"), function (event) {
      event.preventDefault();
      var item = this.closest("[data-component~=\"".concat(namespace, "-data-table-item\"][data-hash=\"").concat(_this.hash, "\"]"));
      var sibling = this.getAttribute('data-direction') === 'next' ? item.nextElementSibling : item.previousElementSibling;
      if (!sibling) {
        return;
      }
      var open = sibling.querySelector("[data-component~=\"".concat(namespace, "-data-table-item-open-modal\"][data-hash=\"").concat(_this.hash, "\"]"));
      if (open) {
        $(open).trigger('click'); // someday jQuery might be removed...
      }
    });
    return this;
  };

  /**
   * Output custom current values into modal opener button
   * @param {HTMLElement} modal - modal section to work with
   * @returns {this} self instance
   */
  Plugin.prototype.exposeCurrentValues = function (modal) {
    var _this = this;
    var selector = modal || "[data-component~=\"".concat(namespace, "-data-table-item-modal\"][data-hash=\"").concat(this.hash, "\"]");
    this.$element.find(selector).each(function () {
      var values = [];
      var haveManualDefinedValues = false;
      $(this).find("tbody [data-component~=\"".concat(namespace, "-data-table-body-item\"][data-hash=\"").concat(_this.hash, "\"] > td > :input:not([type=\"hidden\"], [type=\"checkbox\"])")).each(function () {
        if (this.value) {
          if (this.nodeName.toLowerCase() === 'select') {
            if (this.multiple) {
              if (this.selectedOptions.length > 0) {
                haveManualDefinedValues = true;
              }
            } else {
              if (!this.options[this.selectedIndex].defaultSelected) {
                haveManualDefinedValues = true;
              }
            }
            values.push(this.options[this.selectedIndex].text);
          } else {
            haveManualDefinedValues = true;
            values.push(this.value);
          }
        }
      });
      _this.element.querySelector("[href=\"#".concat(this.getAttribute('id'), "\"][data-hash=\"").concat(_this.hash, "\"]")).setAttribute('data-name', haveManualDefinedValues ? values.join('; ') : '');
    });
    return this;
  };

  /**
   * Add a new element in the table
   * @param {Object} $insertAfter - jQuery element
   * @param {Object} $template - jQuery element
   * @returns {Object} jQuery clone element
   */
  Plugin.prototype.addItem = function ($insertAfter) {
    var $template = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.$template;
    $insertAfter = $insertAfter.length !== 0 ? $insertAfter : this.$body.children("[data-component~=\"".concat(namespace, "-data-table-item\"]:last"));
    if ($template.length === 0) {
      $template = $insertAfter;
    }
    var $newItem = $template.clone();

    // replace modal and opener elements href and id
    $newItem.find("[data-component~=\"".concat(namespace, "-data-table-item-open-modal\"]")).each(function () {
      var modal = this.nextElementSibling;
      if (modal) {
        var rand = Math.random().toString(36).substr(2);
        this.setAttribute('href', this.getAttribute('href') + "-".concat(rand.toString()));
        modal.setAttribute('id', modal.getAttribute('id') + "-".concat(rand.toString()));
      }
    });

    // insert the clone element
    $newItem.insertAfter($insertAfter);
    this.recalculateIds();
    this.$element.trigger("added.item.dataTable.".concat(namespace), [this, $newItem]);
    return $newItem;
  };

  /**
   * Remove element from the table
   * @param {Object} $item - jQuery element
   * @returns {this} self instance
   */
  Plugin.prototype.removeItem = function ($item) {
    if ($item.is(':only-child')) {
      this.addItem($item);
    }
    $item.remove();
    this.recalculateIds();
    this.$element.trigger("removed.item.dataTable.".concat(namespace), [this]);
    return this;
  };

  /**
   * Clone element from the table
   * @param {Object} $item - jQuery element
   * @returns {this} self instance
   */
  Plugin.prototype.cloneItem = function ($item) {
    var $clone = this.addItem($item, $item);
    this.$element.trigger("cloned.item.dataTable.".concat(namespace), [this, $clone]);
    return this;
  };

  /**
   * Recalculate elements indexes in attributes
   * @returns {this} self instance
   */
  Plugin.prototype.recalculateIds = function () {
    var key = this.element.getAttribute('data-key');
    this.$body.children("[data-component~=\"".concat(namespace, "-data-table-item\"]")).each(function (index) {
      var $element = $(this);
      $element.find(':input').each(function () {
        var regExp = new RegExp("(\\[?)".concat(key.replaceAll('[', '\\[').replaceAll(']', '\\]'), "(]?)\\[-?\\d+\\]"));
        if (this.labels) {
          var _iterator = _createForOfIteratorHelper(this.labels),
            _step;
          try {
            for (_iterator.s(); !(_step = _iterator.n()).done;) {
              var label = _step.value;
              if (label.getAttribute('for')) {
                label.setAttribute('for', label.getAttribute('for').replace(regExp, "".concat(key, "[").concat(index, "]")));
              }
            }
          } catch (err) {
            _iterator.e(err);
          } finally {
            _iterator.f();
          }
        }
        if (this.getAttribute('name')) {
          this.setAttribute('name', this.getAttribute('name').replace(regExp, "".concat(key, "[").concat(index, "]")));
        }
        if (this.getAttribute('id')) {
          this.setAttribute('id', this.getAttribute('id').replace(regExp, "".concat(key, "[").concat(index, "]")));
        }
      });
      $element.find("[data-component~=\"".concat(namespace, "-data-table\"]")).each(function () {
        var tableKey = this.getAttribute('data-key');
        if (tableKey) {
          this.setAttribute('data-key', tableKey.replace(new RegExp("\\[(-?\\d+)\\]"), "[".concat(index, "]")));
        }
      });
    });
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
    return $("[data-component~=\"".concat(namespace, "-data-table\"]")).each(function () {
      return $(this).wcpwDataTable();
    });
  };
  $document.ready(function () {
    return init();
  });
  $document.on("init.dataTable.".concat(namespace), function () {
    return init();
  });
});