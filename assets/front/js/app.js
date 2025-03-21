function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
(function (global, factory) {
  (typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object' && typeof module !== 'undefined' ? module.exports = factory() : typeof define === 'function' && define.amd ? define('src/front/js/app.js', factory) : (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global["src/front/js/app"] = global["src/front/js/app"] || {}, global["src/front/js/app"].js = factory());
})(this, function () {
  'use strict';

  /* WooCommerce Products Wizard Utils
   * Original author: Alex Troll
   * Further changes, comments: mail@troll-winner.com
   */

  /**
   * Compare two objects and return are they equal
   * @param {Object} objectA - object to compare
   * @param {Object} objectB - object to compare
   * @returns {Object} equal or not
   */
  function areObjectsEqual(objectA, objectB) {
    return Object.keys(objectA).every(function (key) {
      return objectB.hasOwnProperty(key) ? _typeof(objectA[key]) === 'object' ? areObjectsEqual(objectA[key], objectB[key]) : objectA[key] === objectB[key] : false;
    });
  }

  /**
   * Extend object properties by other objects
   * @param {Object} args - object to extend
   * @returns {Object} new extended object
   */
  function extendObject() {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    return Object.assign.apply(Object, [{}].concat(args));
  }

  /**
   * Serialize any object
   * @link https://github.com/knowledgecode/jquery-param
   * @param {Object} input - any object to serialize
   * @returns {String} a serialized string
   */
  function objectToQueryParam(input) {
    /* eslint-disable */
    var output = [];
    var add = function add(key, value) {
      value = typeof value === 'function' ? value() : value;
      value = value === null ? '' : value === undefined ? '' : value;
      output[output.length] = encodeURIComponent(key) + '=' + encodeURIComponent(value);
    };
    var _buildParams = function buildParams(prefix, obj) {
      var i, len, key;
      if (prefix) {
        if (Array.isArray(obj)) {
          for (i = 0, len = obj.length; i < len; i++) {
            _buildParams(prefix + '[' + (_typeof(obj[i]) === 'object' && obj[i] ? i : '') + ']', obj[i]);
          }
        } else if (Object.prototype.toString.call(obj) === '[object Object]') {
          for (key in obj) {
            _buildParams(prefix + '[' + key + ']', obj[key]);
          }
        } else {
          add(prefix, obj);
        }
      } else if (Array.isArray(obj)) {
        for (i = 0, len = obj.length; i < len; i++) {
          add(obj[i].name, obj[i].value);
        }
      } else {
        for (key in obj) {
          _buildParams(key, obj[key]);
        }
      }
      return output;
    };
    /* eslint-enable */

    return _buildParams('', input).join('&');
  }

  /**
   * Parse any query data to an object
   * @link https://github.com/cobicarmel/jquery-serialize-object/
   * @param {Object} dataContainer - target
   * @param {String} key - prop key
   * @param {Object} value - prop value
   * @returns {Object} recursive or null
   */
  function parseObject(dataContainer, key, value) {
    var isArrayKey = /^[^\[\]]+\[]/.test(key);
    var isObjectKey = /^[^\[\]]+\[[^\[\]]+]/.test(key);
    var keyName = key.replace(/\[.*/, '');
    if (isArrayKey) {
      if (!dataContainer[keyName]) {
        dataContainer[keyName] = [];
      }
    } else {
      if (!isObjectKey) {
        if (dataContainer.push) {
          dataContainer.push(value);
        } else {
          dataContainer[keyName] = value;
        }
        return null;
      }
      if (!dataContainer[keyName]) {
        dataContainer[keyName] = {};
      }
    }
    var nextKeys = key.match(/\[[^\[\]]*]/g);
    nextKeys[0] = nextKeys[0].replace(/\[|]/g, '');
    return parseObject(dataContainer[keyName], nextKeys.join(''), value);
  }

  /**
   * Parse query string to an object
   * @param {String} string - string to parse
   * @returns {Object} parsed output
   */
  function queryStringToObject(string) {
    var output = {};
    if (!string) {
      return output;
    }

    // need to be sure there are quality symbol everywhere case it's required by json
    var pairs = string.split('&');
    for (var key in pairs) {
      if (!pairs[key].includes('=')) {
        pairs[key] = "".concat(pairs[key], "=");
      }
    }
    string = pairs.join('&');

    /* eslint-disable */
    var data = JSON.parse('{"' + decodeURI(string).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}');
    /* eslint-enable */

    for (var _key2 in data) {
      if (data.hasOwnProperty(_key2)) {
        parseObject(output, _key2, data[_key2]);
      }
    }
    return output;
  }

  /**
   * Get FormData as a recursive object
   * @link https://github.com/cobicarmel/jquery-serialize-object/
   * @param {HTMLFormElement} form - DOM element
   * @returns {Object} form data object
   */
  function serializeObject(form) {
    var formData = new FormData(form);
    var data = {};
    var _iterator = _createForOfIteratorHelper(formData.entries()),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var pair = _step.value;
        parseObject(data, pair[0], pair[1]);
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
    return data;
  }

  /**
   * Set element HTML content
   * @param {HTMLElement} element - html element to work with
   * @param {String} html - html to insert
   */
  function setElementHTML(element, html) {
    element.textContent = '';
    element.insertAdjacentHTML('afterbegin', html);
  }

  /**
   * Replace element by HTML string
   * @param {HTMLElement} element - html element to work with
   * @param {String} html - html to insert
   */
  function replaceElementHTML(element, html) {
    var div = document.createElement('div');
    setElementHTML(div, html);
    element.replaceWith.apply(element, _toConsumableArray(div.childNodes));
    div.remove();
  }

  /**
   * Convert value to boolean, even string
   * @param {String|Boolean} value - value to comb
   * @returns {Boolean} true or false
   */
  function toBoolean(value) {
    return Boolean(JSON.parse(String(value)));
  }

  /**
   * Convert value to object, even JSON string
   * @param {String|Object} value - value to parse
   * @returns {Object}
   */
  function toObject(value) {
    if (_typeof(value) === 'object') {
      return value;
    }
    return JSON.parse(value || '{}');
  }

  /* WooCommerce Products Wizard
   * Original author: Alex Troll
   * Further changes, comments: mail@troll-winner.com
   */
  var WCPW = /*#__PURE__*/function () {
    // <editor-fold desc="Core">
    function WCPW(element) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      _classCallCheck(this, WCPW);
      var defaults = {
        jQueryCompatibility: true,
        ajaxURL: '/wp-admin/admin-ajax.php',
        nonce: '',
        documentNode: document,
        windowNode: window,
        rootSelector: 'html, body',
        ajaxActions: {
          submit: 'wcpwSubmit',
          addToMainCart: 'wcpwAddToMainCart',
          getStep: 'wcpwGetStep',
          getStepPage: 'wcpwGetStepPage',
          skipStep: 'wcpwSkipStep',
          skipAll: 'wcpwSkipAll',
          submitAndSkipAll: 'wcpwSubmitAndSkipAll',
          reset: 'wcpwReset',
          addCartProduct: 'wcpwAddCartProduct',
          removeCartProduct: 'wcpwRemoveCartProduct',
          updateCartProduct: 'wcpwUpdateCartProduct'
        }
      };
      this.element = element;
      this.$element = null;
      this.customOptions = options;
      this.options = Object.assign({}, defaults, options);
    }

    /**
     * Init the instance
     * @returns {this} self instance
     */
    return _createClass(WCPW, [{
      key: "init",
      value: function init() {
        if (this.options.jQueryCompatibility && typeof jQuery !== 'undefined') {
          this.$element = jQuery(this.element);
          this.$element.data('wcpw', this);
        }
        this.hasError = false;
        this.preventAjaxRequest = false;
        this.productsWithError = [];
        this.ajaxRequestsQueue = [];
        this.eventListeners = [];
        return this.initEventListeners().triggerEvent('launched.wcpw');
      }

      /**
       * Makes an ajax-request
       * @param {FormData | Object} requestData - request data to pass
       * @param {Object} options - request options
       * @returns {Promise} ajax request
       */
    }, {
      key: "ajaxRequest",
      value: function ajaxRequest(requestData) {
        var _this2 = this;
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        this.preventAjaxRequest = false;
        var formData = requestData instanceof FormData ? requestData : new FormData();
        var defaultOptions = {
          element: this.element,
          contentMethod: 'setHTML',
          updateQueryArgs: true,
          queryArgs: {},
          passCurrentQueryArgs: true,
          currentQueryArgs: this.getQueryArgs(),
          method: 'post',
          scrollingTopOnUpdate: toBoolean(this.options.scrollingTopOnUpdate),
          scrollingGap: Number(this.options.hasOwnProperty('scrollingUpGap') ? this.options.scrollingUpGap : 0),
          scrollingBehavior: 'smooth',
          scrollingToElement: null,
          passProducts: false,
          errorsAlerting: true,
          errorsLogging: true,
          lazy: false
        };
        options = extendObject(defaultOptions, options);

        // add current query args to request
        if (options.passCurrentQueryArgs && options.currentQueryArgs.get) {
          if (options.currentQueryArgs.has('wcpwPage')) {
            formData.append('wcpwPage', options.currentQueryArgs.get('wcpwPage'));
          }
          if (options.currentQueryArgs.has('wcpwOrderBy')) {
            formData.append('wcpwOrderBy', options.currentQueryArgs.get('wcpwOrderBy'));
          }
        }

        // pass extra args
        if (!(requestData instanceof FormData)) {
          for (var key in requestData) {
            if (requestData.hasOwnProperty(key) && requestData[key] !== null) {
              formData.append(key, typeof requestData[key] !== 'string' ? JSON.stringify(requestData[key]) : requestData[key]);
            }
          }
        }

        // don't pass products if needed
        if (!options.passProducts) {
          formData["delete"]('productsToAdd');
          formData["delete"]('productsToAddChecked');
        }

        // save extra parameters
        for (var _key3 in this.customOptions) {
          if (!formData.has(_key3) && this.customOptions.hasOwnProperty(_key3)) {
            formData.append(_key3, typeof this.customOptions[_key3] !== 'string' ? JSON.stringify(this.customOptions[_key3]) : this.customOptions[_key3]);
          }
        }

        // delete "add-to-cart" to not pass the attached product to the cart via AJAX
        formData["delete"]('add-to-cart');

        // add nonce
        formData.append('nonce', this.options.nonce);
        this.triggerEvent('ajaxRequest.wcpw', {
          formData: formData,
          options: options
        });
        if (this.preventAjaxRequest) {
          this.triggerEvent('ajaxPrevent.wcpw', {
            formData: formData,
            options: options
          });
          return Promise.resolve();
        }
        options.element.classList.add(options.lazy ? 'is-lazy-loading' : 'is-loading');
        options.element.setAttribute('aria-live', 'polite');
        options.element.setAttribute('aria-busy', 'true');
        var request = new Promise(function (resolve, reject) {
          var xhr = new XMLHttpRequest();
          xhr.open(options.method.toUpperCase(), _this2.options.ajaxURL, true);
          xhr.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
              options.element.style.setProperty('--wcpw-loading-progress', String(Math.round(event.loaded / event.total * 100)));
            }
          });
          xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
              resolve(xhr.response);
            } else {
              reject(xhr.status);
            }
          };
          xhr.onerror = function () {
            return reject(xhr.status);
          };
          xhr.send(formData);
        }).then(function (response) {
          try {
            if (typeof response === 'string') {
              response = JSON.parse(response);
            }
            if (_typeof(response) !== 'object') {
              throw new Error('Response parsing error');
            }
          } catch (error) {
            return Promise.reject(error);
          }
          _this2.triggerEvent('ajaxSuccess.wcpw', {
            response: response,
            formData: formData,
            options: options
          });
          var requestIndex = _this2.ajaxRequestsQueue.indexOf(request);
          if (requestIndex > -1) {
            _this2.ajaxRequestsQueue.splice(requestIndex, 1);
          }
          if (options.lazy && _this2.ajaxRequestsQueue.length > 0) {
            return response;
          }
          options.element.classList.remove('is-lazy-loading', 'is-loading');
          options.element.setAttribute('aria-busy', 'false');
          options.element.style.removeProperty('--wcpw-loading-progress');
          if (response.content) {
            switch (options.contentMethod) {
              case 'replaceHTML':
                replaceElementHTML(options.element, response.content);
                break;
              default:
              case 'setHTML':
                setElementHTML(options.element, response.content);
            }
          }

          // scroll navs
          var _iterator2 = _createForOfIteratorHelper(options.element.querySelectorAll('[data-component~="wcpw-nav"]')),
            _step2;
          try {
            for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
              var nav = _step2.value;
              var navList = nav.querySelector('[data-component~="wcpw-nav-list"]');
              if (navList) {
                var active = nav.querySelector('.active');
                if (active) {
                  navList.scrollLeft = nav.querySelector('.active').offsetLeft;
                }
              }
            }

            // scroll window
          } catch (err) {
            _iterator2.e(err);
          } finally {
            _iterator2.f();
          }
          if (options.scrollingTopOnUpdate) {
            var element = options.element.querySelector(options.scrollingToElement || '[data-component~="wcpw-form-step"].is-active');
            if (element && !_this2.isScrolledIntoView(element)) {
              _this2.scrollToElement(element, Number(options.scrollingGap), options.scrollingBehavior);
            }
          }
          if (options.updateQueryArgs) {
            // pass all state data into URL
            if (response instanceof Object && response.hasOwnProperty('stateData')) {
              options.queryArgs = extendObject(options.queryArgs, response.stateData);
            }

            // set all not false attributes
            if (Object.keys(options.queryArgs).filter(function (item) {
              return options.queryArgs[item] !== false;
            }).length !== 0) {
              _this2.setQueryArgs(options.queryArgs);
            }
          }
          _this2.triggerEvent('ajaxCompleted.wcpw', {
            response: response,
            formData: formData,
            options: options
          });
          return response;
        })["catch"](function (error) {
          _this2.triggerEvent('ajaxError.wcpw', {
            error: error,
            formData: formData,
            options: options
          });
          var requestIndex = _this2.ajaxRequestsQueue.indexOf(request);
          if (requestIndex > -1) {
            _this2.ajaxRequestsQueue.splice(requestIndex, 1);
          }
          options.classList.remove('is-lazy-loading', 'is-loading');
          options.setAttribute('aria-busy', 'false');
          options.style.removeProperty('--wcpw-loading-progress');
          if (options.errorsLogging) {
            _this2.options.windowNode.console.error(error);
          }
          if (options.errorsAlerting) {
            _this2.options.windowNode.alert("Unexpected error occurred: ".concat(error));
          }
          return error;
        });
        this.ajaxRequestsQueue.push(request);
        return request;
      }

      /**
       * Delegate an event listener to a target
       * @param {String} action - event action name
       * @param {String} selector - target element selector
       * @param {Function} callback - function to fire
       * @param {Object} options - listener options
       * @returns {this} self instance
       */
    }, {
      key: "delegateEventListener",
      value: function delegateEventListener(action, selector, callback) {
        var options = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {
          capture: false
        };
        var actionName = action.split('.')[0];
        var handler = function handler(event) {
          var target = event.target;
          while (target && !target.matches(selector) && target !== this) {
            target = target.parentElement;
          }
          if (target && target.matches(selector)) {
            callback.call(target, event);
          }
          return this;
        };
        this.eventListeners.push({
          action: action,
          actionName: actionName,
          selector: selector,
          handler: handler
        });
        this.element.addEventListener(actionName, handler, options);
        return this;
      }

      /**
       * Un-delegate an event listener from the target
       * @param {String} action - event action name
       * @param {String} selector - target element selector
       * @param {Object} options - listener options
       * @returns {this} self instance
       */
    }, {
      key: "unDelegateEventListener",
      value: function unDelegateEventListener(action) {
        var selector = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
        var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {
          capture: false
        };
        var events = this.eventListeners.filter(function (item) {
          return item.action === action && (!selector || item.selector === selector);
        });
        if (events[0]) {
          this.element.removeEventListener(events[0].actionName, events[0].handler, options);
        }
        return this;
      }

      /**
       * Add required event listeners
       * @returns {this} self instance
       */
    }, {
      key: "initEventListeners",
      value: function initEventListeners() {
        var _this3 = this;
        var _this = this;

        // browser history handlers
        this.options.windowNode.addEventListener('popstate', function (event) {
          return _this3.popState(event);
        }, false);

        // prevent thumbnail link redirect on click
        this.delegateEventListener('click.thumbnail.product.wcpw', '[data-component~="wcpw-product-thumbnail-link"]', function (event) {
          return event.preventDefault();
        });

        // change the active form item
        this.delegateEventListener('click.product.wcpw', '[data-component~="wcpw-product"]', function () {
          var input = this.querySelector('[data-component~="wcpw-product-choose"][type="radio"]');
          if (input && !input.checked && !input.disabled) {
            input.checked = true;
            input.dispatchEvent(new Event('change'));
          }
        });

        // add product to the cart
        this.delegateEventListener('click.add.product.cart.wcpw', '[data-component~="wcpw-add-cart-product"]', function (event) {
          var _this4 = this;
          if (this.classList.contains('disabled')) {
            return event.preventDefault();
          }
          var otherInputs = [];
          var product = this.closest('[data-component~="wcpw-product"]');
          var _iterator3 = _createForOfIteratorHelper(_this.element.querySelectorAll('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)')),
            _step3;
          try {
            for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
              var _input2 = _step3.value;
              if (!product.contains(_input2)) {
                otherInputs.push(_input2);
              }
            }
          } catch (err) {
            _iterator3.e(err);
          } finally {
            _iterator3.f();
          }
          if (!_this.options.documentNode.querySelector('#' + this.getAttribute('form')).checkValidity()) {
            var _iterator4 = _createForOfIteratorHelper(otherInputs),
              _step4;
            try {
              for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
                var input = _step4.value;
                input.disabled = false;
              }
            } catch (err) {
              _iterator4.e(err);
            } finally {
              _iterator4.f();
            }
            return this;
          }
          for (var _i = 0, _otherInputs = otherInputs; _i < _otherInputs.length; _i++) {
            var _input = _otherInputs[_i];
            _input.disabled = false;
          }
          this.classList.add('is-loading');
          this.setAttribute('aria-busy', 'true');
          event.preventDefault();
          return _this.addCartProduct({
            productToAddKey: this.value
          }, toObject(this.getAttribute('data-add-cart-product-options')))["finally"](function () {
            _this4.classList.remove('is-loading');
            _this4.setAttribute('aria-busy', 'false');
          });
        });

        // update product in the cart
        this.delegateEventListener('click.update.product.cart.wcpw', '[data-component~="wcpw-update-cart-product"]', function (event) {
          var _this5 = this;
          if (this.classList.contains('disabled')) {
            return event.preventDefault();
          }
          var otherInputs = [];
          var product = this.closest('[data-component~="wcpw-product"]');
          var _iterator5 = _createForOfIteratorHelper(_this.element.querySelectorAll('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)')),
            _step5;
          try {
            for (_iterator5.s(); !(_step5 = _iterator5.n()).done;) {
              var _input6 = _step5.value;
              if (!product.contains(_input6)) {
                otherInputs.push(_input6);
              }
            }
          } catch (err) {
            _iterator5.e(err);
          } finally {
            _iterator5.f();
          }
          for (var _i2 = 0, _otherInputs2 = otherInputs; _i2 < _otherInputs2.length; _i2++) {
            var input = _otherInputs2[_i2];
            input.disabled = true;
          }
          for (var _i3 = 0, _otherInputs3 = otherInputs; _i3 < _otherInputs3.length; _i3++) {
            var _input3 = _otherInputs3[_i3];
            _input3.disabled = true;
          }
          if (!_this.options.documentNode.querySelector('#' + this.getAttribute('form')).checkValidity()) {
            var _iterator6 = _createForOfIteratorHelper(otherInputs),
              _step6;
            try {
              for (_iterator6.s(); !(_step6 = _iterator6.n()).done;) {
                var _input4 = _step6.value;
                _input4.disabled = false;
              }
            } catch (err) {
              _iterator6.e(err);
            } finally {
              _iterator6.f();
            }
            return this;
          }
          for (var _i4 = 0, _otherInputs4 = otherInputs; _i4 < _otherInputs4.length; _i4++) {
            var _input5 = _otherInputs4[_i4];
            _input5.disabled = false;
          }
          this.classList.add('is-loading');
          this.setAttribute('aria-busy', 'true');
          event.preventDefault();
          return _this.updateCartProduct({
            productCartKey: this.value
          }, toObject(this.getAttribute('data-update-cart-product-options')))["finally"](function () {
            _this5.classList.remove('is-loading');
            _this5.setAttribute('aria-busy', 'false');
          });
        });

        // remove product from the cart
        this.delegateEventListener('click.remove.product.cart.wcpw', '[data-component~="wcpw-remove-cart-product"]', function (event) {
          var _this6 = this;
          event.preventDefault();
          this.classList.add('is-loading');
          this.setAttribute('aria-busy', 'true');
          return _this.removeCartProduct({
            productCartKey: this.value
          }, toObject(this.getAttribute('data-remove-cart-product-options')))["finally"](function () {
            _this6.classList.remove('is-loading');
            _this6.setAttribute('aria-busy', 'false');
          });
        });

        // nav item click
        this.delegateEventListener('click.nav.wcpw', '[data-component~="wcpw-nav-item"]', function (event) {
          var action = this.getAttribute('data-nav-action');
          var data = {
            action: action
          };
          if (!_this.options.documentNode.querySelector('#' + this.getAttribute('form')).checkValidity() && ['submit', 'add-to-main-cart', 'add-to-main-cart-repeat'].indexOf(action) !== -1) {
            return this;
          }
          event.preventDefault();
          if (this.getAttribute('data-nav-id')) {
            data.stepId = this.getAttribute('data-nav-id');
          }
          return _this.navRouter(data);
        });

        // pagination link click
        this.delegateEventListener('click.pagination.wcpw', '[data-component~="wcpw-form-pagination-link"]', function (event) {
          event.preventDefault();
          var queryArgs = _this.getQueryArgs();
          var stepId = this.getAttribute('data-step-id');
          var page = this.getAttribute('data-page');
          var pages = {};

          // change page query
          if (queryArgs.get && queryArgs.has('wcpwPage') && queryArgs.get('wcpwPage')) {
            pages = queryStringToObject(queryArgs.get('wcpwPage'));
          }
          pages[stepId] = page;
          var wcpwPage = objectToQueryParam(pages);
          return _this.getStepPage({
            stepId: stepId,
            page: page,
            wcpwPage: wcpwPage
          }, {
            element: this.closest('[data-component="wcpw-form-step"]'),
            contentMethod: 'replaceHTML',
            queryArgs: {
              wcpwPage: wcpwPage
            },
            scrollingTopOnUpdate: true,
            scrollingToElement: "[data-component~=\"wcpw-form-step\"][data-id=\"".concat(stepId, "\"]")
          });
        });

        // toggle element
        this.delegateEventListener('click.toggle.wcpw', '[data-component~="wcpw-toggle"]', function (event) {
          event.preventDefault();
          var targetSelector = this.getAttribute('data-toggle-target') || this.getAttribute('href');
          var target = _this.element.querySelector(targetSelector);
          var isClosed = target.getAttribute('aria-expanded') === 'false';
          this.setAttribute('aria-expanded', isClosed ? 'true' : 'false');
          target.setAttribute('aria-expanded', isClosed ? 'true' : 'false');
          _this.options.documentNode.cookie = "".concat(targetSelector, "-expanded=").concat(String(isClosed), "; path=/");
          _this.triggerEvent('toggle.wcpw', {
            element: this,
            target: target
          });
        });

        // scroll to element
        this.delegateEventListener('click.scrollToElement.wcpw', '[data-component~="wcpw-scroll-to-element"]', function (event) {
          event.preventDefault();
          var targetSelector = this.getAttribute('data-scroll-to-element-target') || this.getAttribute('href');
          var target = _this.element.querySelector(targetSelector);
          if (target && !_this.isScrolledIntoView(target)) {
            _this.scrollToElement(target);
            _this.triggerEvent('scrollToElement.wcpw', {
              element: this,
              target: target
            });
          }
        });
        return this;
      }

      /**
       * Dispatch an event
       * @param {String} name - event name
       * @param {Object} options - object of arguments
       * @returns {this} self instance
       */
    }, {
      key: "triggerEvent",
      value: function triggerEvent(name) {
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        if (this.$element) {
          this.$element.trigger(name, Object.values(extendObject(options)));
        }
        this.element.dispatchEvent(new CustomEvent(name, {
          bubbles: true,
          detail: extendObject({
            instance: this
          }, options)
        }));
        return this;
      }

      /**
       * Get a step by the previous state
       * @param {PopStateEvent} event - window history pop event
       * @returns {Promise} ajax request
       */
    }, {
      key: "popState",
      value: function popState(event) {
        var requestArgs = {
          action: this.options.ajaxActions.getStep
        };
        var openingPath = event && event.state && event.state.path;
        if (!openingPath) {
          return Promise.resolve();
        }
        var queryArgs = new URL(openingPath).searchParams;
        if (queryArgs.has('wcpwStep')) {
          requestArgs.stepId = queryArgs.get('wcpwStep');
        }
        if (queryArgs.has('wcpwPages')) {
          requestArgs.page = queryStringToObject(queryArgs.get('wcpwPages'))[requestArgs.stepId];
        }
        if (queryArgs.has('wcpwOrderBy')) {
          requestArgs.orderby = queryStringToObject(queryArgs.get('wcpwOrderBy'))[requestArgs.stepId];
        }
        return this.ajaxRequest(requestArgs, {
          updateQueryArgs: false
        });
      }
      // </editor-fold>

      // <editor-fold desc="Product actions">
      /**
       * Add form product to the cart
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "addCartProduct",
      value: function addCartProduct() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var defaultOptions = {
          behavior: 'default',
          passProducts: true
        };
        options = extendObject(defaultOptions, options);

        // change the action to submit
        switch (options.behavior) {
          default:
          case 'default':
            return this.submit(extendObject({
              action: this.options.ajaxActions.addCartProduct
            }, data), extendObject({
              scrollingTopOnUpdate: false
            }, options));
          case 'submit':
            return this.submit(data, options);
          case 'add-to-main-cart':
            return this.addToMainCart(data, options);
        }
      }

      /**
       * Update form product in the cart
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "updateCartProduct",
      value: function updateCartProduct() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var defaultOptions = {
          behavior: 'default',
          passProducts: true
        };
        options = extendObject(defaultOptions, options);

        // change the action to submit
        switch (options.behavior) {
          default:
          case 'default':
            return this.submit(extendObject({
              action: this.options.ajaxActions.updateCartProduct
            }, data), extendObject({
              scrollingTopOnUpdate: false
            }, options));
          case 'submit':
            return this.submit(data, options);
          case 'add-to-main-cart':
            return this.addToMainCart(data, options);
        }
      }

      /**
       * Remove form product from the cart
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "removeCartProduct",
      value: function removeCartProduct() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var defaultOptions = {
          scrollingTopOnUpdate: false,
          passProducts: true
        };
        data = extendObject({
          action: this.options.ajaxActions.removeCartProduct
        }, data);
        options = extendObject(defaultOptions, options);

        // make custom request instead of the form submit
        return this.ajaxRequest(data, options);
      }
      // </editor-fold>

      // <editor-fold desc="Main actions">
      /**
       * Add selected products to the main cart
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "addToMainCart",
      value: function addToMainCart() {
        var _this7 = this;
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var defaultOptions = {
          preventRedirect: false,
          passProducts: true
        };
        data = extendObject({
          action: this.options.ajaxActions.addToMainCart
        }, data);
        options = extendObject(defaultOptions, options);
        var result = this.submit(data, options);
        this.triggerEvent('addToMainCart.wcpw', {
          data: data,
          result: result
        });
        if (!result) {
          return Promise.resolve();
        }
        return result.then(function (response) {
          // has some product errors
          if (response instanceof Object && response.hasError || _this7.hasError) {
            _this7.triggerEvent('addToMainCartError.wcpw', {
              data: data,
              response: response
            });
            return response;
          }
          if (!options.preventRedirect && response instanceof Object && !response.preventRedirect && response.hasOwnProperty('finalRedirectURL') && response.finalRedirectURL) {
            _this7.triggerEvent('addToMainCartRedirect.wcpw', {
              data: data,
              response: response
            });
            _this7.options.documentNode.location = response.finalRedirectURL;
          }
          return response;
        });
      }

      /**
       * Send custom products from the active step to the wizard cart
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request or false
       */
    }, {
      key: "submit",
      value: function submit() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        // reset error state
        this.hasError = false;
        this.productsWithError = [];
        var form = this.element.querySelector('[data-component~="wcpw-form"]') || this.options.documentNode.querySelector('[data-component~="wcpw-form"]');
        var formData = serializeObject(form);
        var defaultData = {
          action: this.options.ajaxActions.submit,
          productToAddKey: null,
          productsToAdd: [],
          productsToAddChecked: []
        };
        var defaultOptions = {
          passProducts: true
        };
        data = extendObject(defaultData, data, formData);
        options = extendObject(defaultOptions, options);
        if (data.productToAddKey) {
          // keep only one product by id
          for (var key in data.productsToAdd) {
            if (data.productsToAdd.hasOwnProperty(key)) {
              var product = data.productsToAdd[key];
              if ("".concat(product.step_id, "-").concat(product.product_id) !== data.productToAddKey) {
                delete data.productsToAdd[key];
              } else {
                data.productsToAddChecked = _defineProperty({}, product.step_id, [product.product_id]);
              }
            }
          }
        } else {
          delete data.productToAddKey;
        }
        this.triggerEvent('submit.wcpw', {
          data: data
        });

        // has some errors
        if (this.hasError) {
          this.triggerEvent('submitError.wcpw', {
            data: data
          });
          return Promise.resolve();
        }

        // send ajax
        return this.ajaxRequest(data, options);
      }

      /**
       * Route to the required navigation event
       * @param {Object} args - object of arguments
       * @param {Object} options - object of method options
       * @returns {Object} nav function
       */
    }, {
      key: "navRouter",
      value: function navRouter() {
        var args = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var action = args.action;

        // action will be added by a method
        delete args.action;
        switch (action) {
          case 'skip-step':
            return this.skipStep(args, options);
          case 'skip-all':
            return this.skipAll(args, options);
          case 'submit-and-skip-all':
            return this.submitAndSkipAll(args, options);
          case 'submit':
            return this.submit(args, options);
          case 'add-to-main-cart':
            return this.addToMainCart(args, options);
          case 'reset':
            return this.reset(args, options);
          case 'none':
            return null;
          case 'get-step':
          default:
            return this.getStep(args, options);
        }
      }

      /**
       * Skip form to the next step without adding products to the wizard cart
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "skipStep",
      value: function skipStep() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        data = extendObject({
          action: this.options.ajaxActions.skipStep
        }, data);
        return this.ajaxRequest(data, options);
      }

      /**
       * Submit and skip form to the last step
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "submitAndSkipAll",
      value: function submitAndSkipAll() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        data = extendObject({
          action: this.options.ajaxActions.submitAndSkipAll
        }, data);
        return this.submit(data, options);
      }

      /**
       * Skip form to the last step without adding products to the wizard cart
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "skipAll",
      value: function skipAll() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        data = extendObject({
          action: this.options.ajaxActions.skipAll
        }, data);
        return this.ajaxRequest(data, options);
      }

      /**
       * Get step content by the id
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "getStep",
      value: function getStep() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        data = extendObject({
          action: this.options.ajaxActions.getStep
        }, data);
        return this.ajaxRequest(data, options);
      }

      /**
       * Get step page content by the id
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "getStepPage",
      value: function getStepPage() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        data = extendObject({
          action: this.options.ajaxActions.getStepPage
        }, data);
        return this.ajaxRequest(data, options);
      }

      /**
       * Reset form to the initial state
       * @param {Object} data - object of arguments
       * @param {Object} options - object of method options
       * @returns {Promise} ajax request
       */
    }, {
      key: "reset",
      value: function reset() {
        var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        data = extendObject({
          action: this.options.ajaxActions.reset
        }, data);
        return this.ajaxRequest(data, options);
      }
      // </editor-fold>

      // <editor-fold desc="Utils">
      /**
       * Get current URL search params
       * @param {String} search - GET string to parse
       * @returns {Object} URLSearchParams
       */
    }, {
      key: "getQueryArgs",
      value: function getQueryArgs() {
        var search = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.options.windowNode.location.search;
        if (typeof URLSearchParams === 'undefined') {
          return {};
        }
        return new URLSearchParams(search);
      }

      /**
       * Set URL request parameter value
       * @param {Object} args - key=>value pairs of params
       * @returns {this} self instance
       */
    }, {
      key: "setQueryArgs",
      value: function setQueryArgs(args) {
        if (!this.options.windowNode.history || !this.options.windowNode.history.pushState) {
          return this;
        }
        var queryArgs = this.getQueryArgs();
        if (!queryArgs.get) {
          return this;
        }
        for (var key in args) {
          if (args.hasOwnProperty(key)) {
            if (typeof args[key] === 'boolean' && !args[key]) {
              queryArgs["delete"](key);
            } else {
              queryArgs.set(key, args[key]);
            }
          }
        }
        var location = this.options.windowNode.location;
        var path = "".concat(location.protocol, "//").concat(location.host).concat(location.pathname, "?").concat(queryArgs.toString());
        this.options.windowNode.history.pushState({
          path: path
        }, '', path);
        return this;
      }

      /**
       * Send vibration signal
       * @param {Array} args - vibration pattern as duration, pause, duration,..
       * @returns {this} self instance
       */
    }, {
      key: "vibrate",
      value: function vibrate() {
        var args = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [200];
        if ('vibrate' in this.options.windowNode.navigator) {
          this.options.windowNode.navigator.vibrate(args);
        } else if ('oVibrate' in this.options.windowNode.navigator) {
          this.options.windowNode.navigator.oVibrate(args);
        } else if ('mozVibrate' in this.options.windowNode.navigator) {
          this.options.windowNode.navigator.mozVibrate(args);
        } else if ('webkitVibrate' in this.options.windowNode.navigator) {
          this.options.windowNode.navigator.webkitVibrate(args);
        }
        return this;
      }

      /**
       * Is element on the screen
       * @param {HTMLElement} element - element to check
       * @param {Boolean} strict - check element bottom position also
       * @returns {Boolean} function result
       */
    }, {
      key: "isScrolledIntoView",
      value: function isScrolledIntoView(element) {
        var strict = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
        var rect = element.getBoundingClientRect();
        return !strict && rect.top >= 0 && rect.top <= this.options.windowNode.innerHeight || strict && rect.top >= 0 && rect.bottom <= this.options.windowNode.innerHeight;
      }

      /**
       * Scroll window screen to element
       * @param {HTMLElement} element - scroll to element
       * @param {Number} gap - top space gap
       * @param {String} behavior - animation behavior
       * @returns {this} self instance
       */
    }, {
      key: "scrollToElement",
      value: function scrollToElement(element) {
        var gap = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
        var behavior = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'smooth';
        var reduceMotion = this.options.windowNode.matchMedia('(prefers-reduced-motion: reduce)') === true || this.options.windowNode.matchMedia('(prefers-reduced-motion: reduce)').matches === true;
        this.options.windowNode.scrollTo({
          top: element.getBoundingClientRect().top + this.options.windowNode.scrollY - Number(gap),
          behavior: reduceMotion ? 'instant' : behavior
        });
        return this;
      }
      // </editor-fold>
    }]);
  }();
  /* WooCommerce Products Wizard Product Variation
   * Original author: Alex Troll
   * Further changes, comments: mail@troll-winner.com
   */
  var WCPWVariationForm = /*#__PURE__*/function () {
    function WCPWVariationForm(element) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      _classCallCheck(this, WCPWVariationForm);
      var defaults = {
        jQueryCompatibility: true
      };
      this.element = element;
      this.$element = null;
      this.customOptions = options;
      this.options = Object.assign({}, defaults, options);
    }

    /**
     * Init the instance
     * @returns {this} self instance
     */
    return _createClass(WCPWVariationForm, [{
      key: "init",
      value: function init() {
        if (this.options.jQueryCompatibility && typeof jQuery !== 'undefined') {
          this.$element = jQuery(this.element);
        }
        this.eventListeners = [];
        this.$reset = this.element.querySelectorAll('[data-component~="wcpw-product-variations-reset"]');
        this.id = this.element.querySelector('[data-component~="wcpw-product-variations-variation-id"]');
        this.$input = this.element.querySelectorAll('[data-component~="wcpw-product-variations-item-input"]');
        this.$variationItem = this.element.querySelectorAll('[data-component~="wcpw-product-variations-item"]');
        this.$variationItemValue = this.element.querySelectorAll('[data-component~="wcpw-product-variations-item-value"]'); //eslint-disable-line
        this.product = this.element.closest('[data-component~="wcpw-product"]');
        this.$productPrice = this.product.querySelectorAll('[data-component~="wcpw-product-price"]');
        this.$productQuantity = this.product.querySelector('[data-component~="wcpw-product-quantity"] input:not([type="button"])'); //eslint-disable-line
        this.$productDescription = this.product.querySelectorAll('[data-component~="wcpw-product-description"]');
        this.$productAvailability = this.product.querySelectorAll('[data-component~="wcpw-product-availability"]');
        this.$productSku = this.product.querySelectorAll('[data-component~="wcpw-product-sku"]');
        this.$productAddToCart = this.product.querySelectorAll('[data-component~="wcpw-add-cart-product"]');
        this.$productChoose = this.product.querySelectorAll('[data-component~="wcpw-product-choose"]');
        this.$productImage = this.product.querySelectorAll('[data-component~="wcpw-product-thumbnail-image"]');
        this.$productLink = this.product.querySelectorAll('[data-component~="wcpw-product-thumbnail-link"]');
        return this.initEventListeners().triggerEvent('launched.variationForm.wcpw');
      }

      /**
       * Delegate an event listener to a target
       * @param {String} action - event action name
       * @param {String} selector - target element selector
       * @param {Function} callback - function to fire
       * @param {Object} options - listener options
       * @returns {this} self instance
       */
    }, {
      key: "delegateEventListener",
      value: function delegateEventListener(action, selector, callback) {
        var options = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {
          capture: false
        };
        var actionName = action.split('.')[0];
        var handler = function handler(event) {
          var target = event.target;
          while (target && !target.matches(selector) && target !== this) {
            target = target.parentElement;
          }
          if (target && target.matches(selector)) {
            callback.call(target, event);
          }
          return this;
        };
        this.eventListeners.push({
          action: action,
          actionName: actionName,
          selector: selector,
          handler: handler
        });
        this.element.addEventListener(actionName, handler, options);
        return this;
      }

      /**
       * Un-delegate an event listener from the target
       * @param {String} action - event action name
       * @param {String} selector - target element selector
       * @param {Object} options - listener options
       * @returns {this} self instance
       */
    }, {
      key: "unDelegateEventListener",
      value: function unDelegateEventListener(action) {
        var selector = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
        var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {
          capture: false
        };
        var events = this.eventListeners.filter(function (item) {
          return item.action === action && (!selector || item.selector === selector);
        });
        if (events[0]) {
          this.element.removeEventListener(events[0].actionName, events[0].handler, options);
        }
        return this;
      }

      /**
       * Add required event listeners
       * @returns {this} self instance
       */
    }, {
      key: "initEventListeners",
      value: function initEventListeners() {
        var _this8 = this;
        var _this = this;

        // unbind any existing events
        this.unDelegateEventListener('change.input.variationForm.wcpw', '[data-component~="wcpw-product-variations-item-input"]');

        // bind events
        // check variations
        this.element.addEventListener('check_variations', function (event) {
          var currentSettings = {};
          var allSet = true;
          var _iterator7 = _createForOfIteratorHelper(_this8.$input),
            _step7;
          try {
            for (_iterator7.s(); !(_step7 = _iterator7.n()).done;) {
              var _element4 = _step7.value;
              if (_element4.tagName === 'SELECT' && (!_element4.value || _element4.value.length === 0)) {
                allSet = false;
              }
              if (_element4.tagName === 'SELECT' || _element4.checked) {
                currentSettings[_element4.getAttribute('data-name')] = _element4.value;
              }
            }
          } catch (err) {
            _iterator7.e(err);
          } finally {
            _iterator7.f();
          }
          var matchingVariations = _this8.findMatchingVariations(JSON.parse(_this8.element.getAttribute('data-product_variations') || '{}'), currentSettings);
          if (allSet) {
            var variation = null;
            for (var key in matchingVariations) {
              if (!matchingVariations.hasOwnProperty(key)) {
                continue;
              }
              var currentCopy = extendObject(currentSettings);
              var attributesCopy = extendObject(matchingVariations[key].attributes);
              for (var attributeCopyItem in attributesCopy) {
                if (!attributesCopy.hasOwnProperty(attributeCopyItem)) {
                  continue;
                }

                // change "any" value to compare
                if (attributesCopy[attributeCopyItem] === '') {
                  attributesCopy[attributeCopyItem] = currentCopy[attributeCopyItem];
                }
              }

              // find the same variation as for the current properties
              if (areObjectsEqual(attributesCopy, currentCopy)) {
                variation = matchingVariations[key];
                break;
              }
            }
            if (variation) {
              // Found - set ID
              _this8.id.value = variation.variation_id;
              _this8.triggerEvent('found_variation', {
                variation: variation
              });
            } else if (!event.detail.focus) {
              // Nothing found - reset fields
              _this8.triggerEvent('reset_image');
              _this8.triggerEvent('hide_variation');
            }
          } else {
            if (!event.detail.focus) {
              _this8.triggerEvent('reset_image');
              _this8.triggerEvent('hide_variation');
            }
            if (!event.detail.exclude) {
              // reset html
              var _iterator8 = _createForOfIteratorHelper(_this8.$productPrice),
                _step8;
              try {
                for (_iterator8.s(); !(_step8 = _iterator8.n()).done;) {
                  var element = _step8.value;
                  setElementHTML(element, element.getAttribute('data-default'));
                }
              } catch (err) {
                _iterator8.e(err);
              } finally {
                _iterator8.f();
              }
              var _iterator9 = _createForOfIteratorHelper(_this8.$productDescription),
                _step9;
              try {
                for (_iterator9.s(); !(_step9 = _iterator9.n()).done;) {
                  var _element = _step9.value;
                  setElementHTML(_element, _element.getAttribute('data-default'));
                }
              } catch (err) {
                _iterator9.e(err);
              } finally {
                _iterator9.f();
              }
              var _iterator10 = _createForOfIteratorHelper(_this8.$productAvailability),
                _step10;
              try {
                for (_iterator10.s(); !(_step10 = _iterator10.n()).done;) {
                  var _element2 = _step10.value;
                  setElementHTML(_element2, _element2.getAttribute('data-default'));
                }
              } catch (err) {
                _iterator10.e(err);
              } finally {
                _iterator10.f();
              }
              var _iterator11 = _createForOfIteratorHelper(_this8.$productSku),
                _step11;
              try {
                for (_iterator11.s(); !(_step11 = _iterator11.n()).done;) {
                  var _element3 = _step11.value;
                  setElementHTML(_element3, _element3.getAttribute('data-default'));
                }
              } catch (err) {
                _iterator11.e(err);
              } finally {
                _iterator11.f();
              }
            }
          }
          _this8.triggerEvent('update_variation_values', {
            variations: extendObject(matchingVariations),
            currentSettings: extendObject(currentSettings)
          });

          // toggle add-to-cart controls according availability
          var _iterator12 = _createForOfIteratorHelper(_this8.$productAddToCart),
            _step12;
          try {
            for (_iterator12.s(); !(_step12 = _iterator12.n()).done;) {
              var _element5 = _step12.value;
              // if disabled upper
              if (_element5.disabled) {
                continue;
              }
              _element5.disabled = !_this8.id.value;
            }
          } catch (err) {
            _iterator12.e(err);
          } finally {
            _iterator12.f();
          }
          var _iterator13 = _createForOfIteratorHelper(_this8.$productChoose),
            _step13;
          try {
            for (_iterator13.s(); !(_step13 = _iterator13.n()).done;) {
              var _element6 = _step13.value;
              // if disabled upper
              if (_element6.disabled) {
                continue;
              }
              _element6.disabled = !_this8.id.value;
            }
          } catch (err) {
            _iterator13.e(err);
          } finally {
            _iterator13.f();
          }
        });

        // reset product image
        this.element.addEventListener('reset_image', function () {
          return _this8.updateImage(false);
        });

        // Disable option fields that are unavailable for current set of attributes
        this.element.addEventListener('update_variation_values', function (event) {
          var variations = event.detail.variation;
          if (!variations || Object.keys(variations).length <= 0) {
            return _this8;
          }
          var isDefaultValue = true;

          // Loop through selects and disable/enable options based on selections
          var _iterator14 = _createForOfIteratorHelper(_this8.$variationItem),
            _step14;
          try {
            var _loop = function _loop() {
              var element = _step14.value;
              var currentAttrName = element.getAttribute('data-name');
              var $values = element.querySelectorAll('[data-component~="wcpw-product-variations-item-value"]');
              var _iterator16 = _createForOfIteratorHelper($values),
                _step16;
              try {
                for (_iterator16.s(); !(_step16 = _iterator16.n()).done;) {
                  var _value2 = _step16.value;
                  _value2.classList.remove('active');
                  _value2.disabled = false;
                }

                // Loop through variations
              } catch (err) {
                _iterator16.e(err);
              } finally {
                _iterator16.f();
              }
              for (var variationKey in variations) {
                if (!variations.hasOwnProperty(variationKey)) {
                  continue;
                }
                var attributes = extendObject(variations[variationKey].attributes);
                for (var attrName in attributes) {
                  if (!attributes.hasOwnProperty(attrName) || attrName !== currentAttrName) {
                    continue;
                  }
                  var attrVal = attributes[attrName];
                  if (!attrVal) {
                    var currentCopy = extendObject(currentSettings);
                    var attributesCopy = extendObject(attributes);
                    delete attributesCopy[attrName];
                    delete currentCopy[attrName];
                    for (var attributeCopyItem in attributesCopy) {
                      if (!attributesCopy.hasOwnProperty(attributeCopyItem)) {
                        continue;
                      }

                      // remove "any" values too
                      if (attributesCopy[attributeCopyItem] === '') {
                        delete attributesCopy[attributeCopyItem];
                        delete currentCopy[attributeCopyItem];
                      }
                    }
                    if (areObjectsEqual(attributesCopy, currentCopy)) {
                      var _iterator17 = _createForOfIteratorHelper($values),
                        _step17;
                      try {
                        for (_iterator17.s(); !(_step17 = _iterator17.n()).done;) {
                          var value = _step17.value;
                          value.classList.add('active');
                        }
                      } catch (err) {
                        _iterator17.e(err);
                      } finally {
                        _iterator17.f();
                      }
                    }
                  }

                  // Decode entities
                  attrVal = decodeURIComponent(attrVal);
                  // Add slashes
                  attrVal = attrVal.replace(/'/g, "\\'");
                  attrVal = attrVal.replace(/"/g, '\\\"');
                  var _iterator18 = _createForOfIteratorHelper($values),
                    _step18;
                  try {
                    for (_iterator18.s(); !(_step18 = _iterator18.n()).done;) {
                      var _value = _step18.value;
                      if (attrVal) {
                        if (_value.value === attrVal) {
                          _value.classList.add('active');
                        }
                      } else {
                        _value.classList.add('active');
                      }
                    }
                  } catch (err) {
                    _iterator18.e(err);
                  } finally {
                    _iterator18.f();
                  }
                }
              }

              // Detach inactive
              var _iterator19 = _createForOfIteratorHelper(element.querySelectorAll('[data-component~="wcpw-product-variations-item-value"]:not(.active)')),
                _step19;
              try {
                for (_iterator19.s(); !(_step19 = _iterator19.n()).done;) {
                  var _value3 = _step19.value;
                  _value3.disabled = true;
                }

                // choose a not-disabled value
              } catch (err) {
                _iterator19.e(err);
              } finally {
                _iterator19.f();
              }
              if (element.tagName === 'SELECT') {
                var activeValue = element.querySelector('option:checked');
                if (!activeValue.getAttribute('selected')) {
                  isDefaultValue = false;
                }
                if (activeValue.disabled) {
                  var otherValue = element.querySelector('option:not(:disabled)');
                  if (otherValue) {
                    // select first available value
                    // skip one tick to finish the current handler
                    setTimeout(function () {
                      element.value = otherValue.value;
                    }, 0);
                  }
                }
              } else {
                var _activeValue = element.querySelector('[data-component~="wcpw-product-variations-item-value"]:checked');
                if (!_activeValue.getAttribute('checked')) {
                  isDefaultValue = false;
                }
                if (_activeValue.disabled) {
                  var _otherValue = element.querySelector('[data-component~="wcpw-product-variations-item-value"]:not(:disabled)');
                  if (_otherValue) {
                    // select first available value
                    // skip one tick to finish the current handler
                    setTimeout(function () {
                      _otherValue.checked = true;
                    }, 0);
                  }
                }
              }
            };
            for (_iterator14.s(); !(_step14 = _iterator14.n()).done;) {
              _loop();
            }

            // show/hide reset button
          } catch (err) {
            _iterator14.e(err);
          } finally {
            _iterator14.f();
          }
          var _iterator15 = _createForOfIteratorHelper(_this8.$reset),
            _step15;
          try {
            for (_iterator15.s(); !(_step15 = _iterator15.n()).done;) {
              var reset = _step15.value;
              reset.setAttribute('hidden', String(isDefaultValue));
            }

            // Custom event for when variations have been updated
          } catch (err) {
            _iterator15.e(err);
          } finally {
            _iterator15.f();
          }
          _this8.triggerEvent('woocommerce_update_variation_values');
          return _this8;
        });

        // show single variation details (price, stock, image)
        this.element.addEventListener('found_variation', function (event) {
          var purchasable = true;
          var variation = event.detail.variation;

          // change price
          if (variation.price_html) {
            var _iterator20 = _createForOfIteratorHelper(_this8.$productPrice),
              _step20;
            try {
              for (_iterator20.s(); !(_step20 = _iterator20.n()).done;) {
                var element = _step20.value;
                setElementHTML(element, variation.price_html);
              }
            } catch (err) {
              _iterator20.e(err);
            } finally {
              _iterator20.f();
            }
          }

          // change min quantity
          if (_this8.$productQuantity && variation.min_qty) {
            _this8.$productQuantity.setAttribute('min', variation.min_qty);
          }

          // change max quantity
          if (_this8.$productQuantity && variation.max_qty) {
            _this8.$productQuantity.setAttribute('max', variation.max_qty);
          }

          // change description - support different versions of woocommerce
          var _iterator21 = _createForOfIteratorHelper(_this8.$productDescription),
            _step21;
          try {
            for (_iterator21.s(); !(_step21 = _iterator21.n()).done;) {
              var _element7 = _step21.value;
              setElementHTML(_element7, variation.description || variation.variation_description || _element7.getAttribute('data-default'));
            }

            // change availability
          } catch (err) {
            _iterator21.e(err);
          } finally {
            _iterator21.f();
          }
          var _iterator22 = _createForOfIteratorHelper(_this8.$productAvailability),
            _step22;
          try {
            for (_iterator22.s(); !(_step22 = _iterator22.n()).done;) {
              var _element8 = _step22.value;
              setElementHTML(_element8, variation.availability_html || _element8.getAttribute('data-default'));
            }

            // change sku
          } catch (err) {
            _iterator22.e(err);
          } finally {
            _iterator22.f();
          }
          var _iterator23 = _createForOfIteratorHelper(_this8.$productSku),
            _step23;
          try {
            for (_iterator23.s(); !(_step23 = _iterator23.n()).done;) {
              var _element9 = _step23.value;
              setElementHTML(_element9, variation.sku || _element9.getAttribute('data-default'));
            }

            // enable or disable the add to cart button and checkbox/radio
          } catch (err) {
            _iterator23.e(err);
          } finally {
            _iterator23.f();
          }
          if (!variation.is_purchasable || !variation.is_in_stock || !variation.variation_is_visible) {
            purchasable = false;
          }

          // toggle add-to-cart controls according availability
          var _iterator24 = _createForOfIteratorHelper(_this8.$productAddToCart),
            _step24;
          try {
            for (_iterator24.s(); !(_step24 = _iterator24.n()).done;) {
              var _element10 = _step24.value;
              _element10.disabled = !purchasable;
            }
          } catch (err) {
            _iterator24.e(err);
          } finally {
            _iterator24.f();
          }
          var _iterator25 = _createForOfIteratorHelper(_this8.$productChoose),
            _step25;
          try {
            for (_iterator25.s(); !(_step25 = _iterator25.n()).done;) {
              var _element11 = _step25.value;
              _element11.disabled = !purchasable;
            }
          } catch (err) {
            _iterator25.e(err);
          } finally {
            _iterator25.f();
          }
          return _this8.updateImage(variation);
        });

        // reset form to default state
        this.element.addEventListener('reset', function () {
          var _iterator26 = _createForOfIteratorHelper(_this8.$variationItemValue),
            _step26;
          try {
            for (_iterator26.s(); !(_step26 = _iterator26.n()).done;) {
              var element = _step26.value;
              var isInput = element.tagName === 'INPUT';
              element.disabled = false;
              element.checked = isInput ? element.defaultChecked : element.defaultSelected;
            }
          } catch (err) {
            _iterator26.e(err);
          } finally {
            _iterator26.f();
          }
          var _iterator27 = _createForOfIteratorHelper(_this8.$reset),
            _step27;
          try {
            for (_iterator27.s(); !(_step27 = _iterator27.n()).done;) {
              var reset = _step27.value;
              reset.setAttribute('hidden', true);
            }
          } catch (err) {
            _iterator27.e(err);
          } finally {
            _iterator27.f();
          }
          _this8.triggerEvent('check_variations');
        });

        // upon changing an option
        this.delegateEventListener('change.input.variationForm.wcpw', '[data-component~="wcpw-product-variations-item-input"]', function () {
          _this.id.value = '';
          _this.triggerEvent('woocommerce_variation_select_change');
          _this.triggerEvent('check_variations', {
            exclude: this.getAttribute('data-name'),
            focus: true
          });
        });

        // reset button click event
        this.delegateEventListener('click.reset.variationForm.wcpw', '[data-component~="wcpw-product-variations-reset"]', function (event) {
          event.preventDefault();
          return _this8.triggerEvent('reset');
        });
        this.triggerEvent('check_variations');
        this.triggerEvent('wc_variation_form');
        return this;
      }

      /**
       * Dispatch an event
       * @param {String} name - event name
       * @param {Object} options - object of arguments
       * @returns {this} self instance
       */
    }, {
      key: "triggerEvent",
      value: function triggerEvent(name) {
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        if (this.$element) {
          this.$element.trigger(name, Object.values(options));
        }
        this.element.dispatchEvent(new CustomEvent(name, {
          bubbles: true,
          detail: extendObject({
            instance: this
          }, options)
        }));
        return this;
      }

      /**
       * Reset a default attribute for an element, so it can be reset later
       * @param {NodeList} elements - html elements collection to work with
       * @param {String} attr - attribute name
       * @returns {this} self instance
       */
    }, {
      key: "resetAttr",
      value: function resetAttr(elements, attr) {
        var _iterator28 = _createForOfIteratorHelper(elements),
          _step28;
        try {
          for (_iterator28.s(); !(_step28 = _iterator28.n()).done;) {
            var element = _step28.value;
            if (element.getAttribute("data-o_".concat(attr))) {
              element.setAttribute(attr, element.getAttribute("data-o_".concat(attr)));
            }
          }
        } catch (err) {
          _iterator28.e(err);
        } finally {
          _iterator28.f();
        }
        return this;
      }

      /**
       * Stores a default attribute for an element, so it can be reset later
       * @param {NodeList} elements - html elements collection to work with
       * @param {String} attr - attribute name
       * @param {String} value - attribute value
       * @returns {this} self instance
       */
    }, {
      key: "setAttr",
      value: function setAttr(elements, attr, value) {
        var _iterator29 = _createForOfIteratorHelper(elements),
          _step29;
        try {
          for (_iterator29.s(); !(_step29 = _iterator29.n()).done;) {
            var element = _step29.value;
            if (element.getAttribute("data-o_".concat(attr))) {
              element.setAttribute("data-o_".concat(attr), !element.getAttribute(attr) ? '' : element.getAttribute(attr));
            }
            if (value === false) {
              element.removeAttribute(attr);
            } else {
              element.setAttribute(attr, value);
            }
          }
        } catch (err) {
          _iterator29.e(err);
        } finally {
          _iterator29.f();
        }
        return this;
      }

      /**
       * Sets product images for the chosen variation
       * @param {Object} variation - variation data
       * @returns {this} self instance
       */
    }, {
      key: "updateImage",
      value: function updateImage(variation) {
        if (variation && variation.image && (variation.image.src || variation.image_src)) {
          this.setAttr(this.$productImage, 'src', variation.image_src || variation.image.src);
          this.setAttr(this.$productImage, 'srcset', variation.image_srcset || variation.image.srcset);
          this.setAttr(this.$productImage, 'sizes', variation.image_sizes || variation.image.sizes);
          this.setAttr(this.$productImage, 'title', variation.image_title || variation.image.title);
          this.setAttr(this.$productImage, 'alt', variation.image_alt || variation.image.alt);
          this.setAttr(this.$productLink, 'href', variation.image_link || variation.image.full_src);
        } else {
          this.resetAttr(this.$productImage, 'src');
          this.resetAttr(this.$productImage, 'srcset');
          this.resetAttr(this.$productImage, 'sizes');
          this.resetAttr(this.$productImage, 'alt');
          this.resetAttr(this.$productLink, 'href');
        }
        return this;
      }

      /**
       * Get product matching variations
       * @param {Array} productVariations - variations collection
       * @param {Object} current - current properties object
       * @returns {Array} matching
       */
    }, {
      key: "findMatchingVariations",
      value: function findMatchingVariations(productVariations, current) {
        var output = [];
        var addedVariationsIds = {};
        for (var variationKey in productVariations) {
          if (!productVariations.hasOwnProperty(variationKey)) {
            continue;
          }
          var variation = productVariations[variationKey];
          for (var currentItem in current) {
            if (!current.hasOwnProperty(currentItem)) {
              continue;
            }
            var attributesCopy = extendObject(variation.attributes);
            var currentCopy = extendObject(current);

            // remove the same property from compare
            delete attributesCopy[currentItem];
            delete currentCopy[currentItem];
            for (var attributeCopyItem in attributesCopy) {
              if (!attributesCopy.hasOwnProperty(attributeCopyItem)) {
                continue;
              }

              // remove "any" values too
              if (attributesCopy[attributeCopyItem] === '') {
                delete attributesCopy[attributeCopyItem];
                delete currentCopy[attributeCopyItem];
              }
            }

            // if the other variation properties are the same as the current then allow this variation
            if (areObjectsEqual(attributesCopy, currentCopy) && !addedVariationsIds.hasOwnProperty(variation.variation_id)) {
              addedVariationsIds[variation.variation_id] = variation.variation_id;
              output.push(variation);
            }
          }
        }
        return output;
      }
    }]);
  }();
  /* WooCommerce Products Wizard global instance and main event handlers
   * Original author: Alex Troll
   * Further changes, comments: mail@troll-winner.com
   */
  var wcpw = {
    windowNode: window,
    documentNode: document,
    $window: typeof jQuery !== 'undefined' ? jQuery(window) : null,
    $document: typeof jQuery !== 'undefined' ? jQuery(document) : null,
    $body: typeof jQuery !== 'undefined' ? jQuery(document.body) : null,
    /**
     * Init all wizards on the page
     * @returns {this} self instance
     */
    init: function init() {
      if (typeof WCPW === 'undefined') {
        this.windowNode.console.error('WCPW class is not exist');
        return this;
      }
      var _iterator30 = _createForOfIteratorHelper(this.documentNode.querySelectorAll('[data-component~="wcpw"]')),
        _step30;
      try {
        for (_iterator30.s(); !(_step30 = _iterator30.n()).done;) {
          var element = _step30.value;
          element.wcpw = new WCPW(element, JSON.parse(element.getAttribute('data-options')) || {});
          element.wcpw.init();
        }
      } catch (err) {
        _iterator30.e(err);
      } finally {
        _iterator30.f();
      }
      return this;
    },
    /**
     * Init variable product forms
     * @param {HTMLCollection} elements - product variation forms
     * @returns {this} self instance
     */
    initVariationForm: function initVariationForm(elements) {
      if (typeof WCPWVariationForm === 'undefined') {
        this.windowNode.console.error('WCPWVariationForm class is not exist');
        return this;
      }
      elements = elements || this.documentNode.querySelectorAll('[data-component~="wcpw-product-variations"]');
      var _iterator31 = _createForOfIteratorHelper(elements),
        _step31;
      try {
        for (_iterator31.s(); !(_step31 = _iterator31.n()).done;) {
          var element = _step31.value;
          element.wcpwVariationForm = new WCPWVariationForm(element, JSON.parse(element.getAttribute('data-options')) || {});
          element.wcpwVariationForm.init();
        }
      } catch (err) {
        _iterator31.e(err);
      } finally {
        _iterator31.f();
      }
      return this;
    },
    /**
     * Launch other scripts on wizard init
     * @param {WCPW} instance - wizard instance object
     * @returns {this} self instance
     */
    onInit: function onInit(instance) {
      // init variation forms
      this.initVariationForm(instance.element.querySelectorAll('[data-component~="wcpw-product-variations"]'));

      // avada lightbox init
      if (typeof this.windowNode.avadaLightBox !== 'undefined' && typeof this.windowNode.avadaLightBox.activate_lightbox !== 'undefined') {
        this.windowNode.avadaLightBox.activate_lightbox(jQuery(instance.element));
      }
    }
  };
  if (typeof wcpw.documentNode.wcpw === 'undefined') {
    wcpw.documentNode.wcpw = wcpw;
  }
  wcpw.documentNode.addEventListener('init.variationForm.wcpw', function () {
    return wcpw.initVariationForm();
  });
  wcpw.documentNode.addEventListener('init.wcpw', function () {
    return wcpw.init();
  });
  wcpw.documentNode.addEventListener('onInit.wcpw', function (event) {
    return wcpw.onInit(event.detail.instance);
  });
  wcpw.documentNode.addEventListener('DOMContentLoaded', function () {
    wcpw.init();
  });
  wcpw.documentNode.addEventListener('launched.wcpw', function (event) {
    var instance = event.detail.instance;
    wcpw.onInit(instance);
  });

  // ajax actions
  wcpw.documentNode.addEventListener('ajaxCompleted.wcpw', function (event) {
    var instance = event.detail.instance;
    var response = event.detail.response;
    event.detail.options;
    wcpw.onInit(instance);
    if (response instanceof Object && response.hasError) {
      var message = instance.element.querySelector('[data-component~="wcpw-message"]');

      // scroll to the message
      if (message && !instance.isScrolledIntoView(message)) {
        instance.scrollToElement(message, instance.options.scrollingUpGap);
      }

      // vibration signal
      instance.vibrate();
    }
  });
  wcpw.documentNode.addEventListener('submitError.wcpw', function (event) {
    var instance = event.detail.instance;
    if (!instance || instance.productsWithError.length <= 0) {
      return;
    }
    var product = instance.element.querySelector("[data-component~=\"wcpw-product\"][data-id=\"".concat(instance.productsWithError[0].product_id, "\"]") + "[data-step-id=\"".concat(instance.productsWithError[0].step_id, "\"]"));
    if (!product) {
      return;
    }

    // scroll window to the product
    if (!instance.isScrolledIntoView(product)) {
      instance.scrollToElement(product, instance.options.scrollingUpGap);
    }

    // vibration signal
    instance.vibrate();
  });
  return wcpw;
});