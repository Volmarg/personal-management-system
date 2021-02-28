/**
 * @link http://www.techlaboratory.net/jquery-smarttab
 * @description the logic provided in here is the original logic taken from the plugin
 *              however some changes were provided so that it can work with the webpack
 *
 */
export default class JquerySmartTab {

    // volmarg - fix adding logic to initial call
    private initialCall: boolean = true;

    private defaults = {
        selected: 0,
        // Initial selected tab, 0 = first tab
        theme: 'default',
        // theme for the tab, related css need to include for other than default theme
        orientation: 'horizontal',
        // Nav menu orientation. horizontal/vertical
        justified: true,
        // Nav menu justification. true/false
        autoAdjustHeight: true,
        // Automatically adjust content height
        backButtonSupport: true,
        // Enable the back button support
        enableURLhash: true,
        // Enable selection of the tab based on url hash
        transition: {
            animation: 'none',
            // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
            speed: '400',
            // Transion animation speed
            easing: '' // Transition animation easing. Not supported without a jQuery easing plugin

        },
        autoProgress: {
            // Auto navigate tabs on interval
            enabled: false,
            // Enable/Disable Auto navigation
            interval: 3500,
            // Auto navigate Interval (used only if "autoProgress" is enabled)
            stopOnFocus: true // Stop auto navigation on focus and resume on outfocus

        },
        keyboardSettings: {
            keyNavigation: true,
            // Enable/Disable keyboard navigation(left and right keys are used if enabled)
            keyLeft: [37],
            // Left key code
            keyRight: [39] // Right key code

        }
    };

    private options;
    private main;
    private nav;
    private tabs;
    private container;
    private pages;
    private current_index;
    private autoProgressId;

    _typeof(obj) {
        "@babel/helpers - typeof";
        if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
            this._typeof = function _typeof(obj) {
                return typeof obj;
            };
        } else {
            this._typeof = function _typeof(obj) {
                return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
            };
        }
        return this._typeof(obj);
    }

    _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    _defineProperties(target, props) {
        for (var i = 0; i < props.length; i++) {
            var descriptor = props[i];
            descriptor.enumerable = descriptor.enumerable || false;
            descriptor.configurable = true;
            if ("value" in descriptor) descriptor.writable = true;
            Object.defineProperty(target, descriptor.key, descriptor);
        }
    }

    _createClass(Constructor, protoProps, staticProps) {
        if (protoProps) this._defineProperties(Constructor.prototype, protoProps);
        if (staticProps) this._defineProperties(Constructor, staticProps);
        return Constructor;
    }

    public initForElementAndOptions(element, options) {
        // this._classCallCheck(this, this.initForElementAndOptions);

        // Merge user settings with default
        this.options = $.extend(true, {}, this.defaults, options); // Main container element

        this.main = $(element); // Navigation bar element

        this.nav = this._getFirstDescendant('.nav'); // Tab anchor elements

        this.tabs = this.nav.find('.nav-link'); // Content container

        this.container = this._getFirstDescendant('.tab-content'); // Content pages

        this.pages = this.container.children('.tab-pane'); // Active Tab index

        this.current_index = null; // Autoprogress timer id

        this.autoProgressId = null; // Assign options

        this._initOptions(); // Initial load

        this._initLoad();
    } // Initial Load Method


    private _initLoad() {
        // Clean the elements
        this.pages.hide();
        this.tabs.removeClass('active'); // Get the initial tab index

        var idx = this._getTabIndex(); // Show the initial tab


        this._showTab(idx);
    } // Initialize options

    private _initOptions() {
        // Set the elements
        this._setElements(); // Assign plugin events


        this._setEvents();
    }

    private _getFirstDescendant(selector) {
        // Check for first level element
        var elm = this.main.children(selector);

        if (elm.length > 0) {
            return elm;
        } // Check for second level element


        this.main.children().each(function (i, n) {
            var tmp = $(n).children(selector);

            if (tmp.length > 0) {
                elm = tmp;
                return false;
            }
        });

        if (elm.length > 0) {
            return elm;
        } // Element not found


        this._showError("Element not found " + selector);

        return false;
    }

    private _setElements() {
        // Set the main element
        this.main.addClass('st');

        if (this.options.justified === true) {
            this.main.addClass('st-justified');
        } else {
            this.main.removeClass('st-justified');
        }

        this._setTheme(this.options.theme);

        this._setOrientation(this.options.orientation);
    }

    private _setEvents() {
        var _this = this;

        // Check if event handler already exists
        if (this.main.data('click-init')) {
            return true;
        } // Flag item to prevent attaching handler again


        this.main.data('click-init', true); // Anchor click event

        $(this.tabs).on("click", function (e) {
            e.preventDefault();

            _this._showTab(_this.tabs.index(e.currentTarget));
        }); // Keyboard navigation event

        if (this.options.keyboardSettings.keyNavigation) {
            $(document).keyup(function (e) {
                _this._keyNav(e);
            });
        } // Back/forward browser button event


        if (this.options.backButtonSupport) {
            $(window).on('hashchange', function (e) {
                var idx = _this._getURLHashIndex();

                if (idx !== false) {
                    e.preventDefault();

                    _this._showTab(idx);
                }
            });
        }

        if (this.options.autoProgress.enabled && this.options.autoProgress.stopOnFocus) {
            $(this.main).on("mouseover", function (e) {
                e.preventDefault();

                _this._stopAutoProgress();
            });
            $(this.main).on("mouseleave", function (e) {
                e.preventDefault();

                _this._startAutoProgress();
            });
        }
    }

    private _showNext() {
        var si = 0; // Find the next showable step

        for (var i = this.current_index + 1; i < this.tabs.length; i++) {
            if (this._isShowable(i)) {
                si = i;
                break;
            }
        }

        this._showTab(si);
    }

    private _showPrevious() {
        var si = this.tabs.length - 1; // Find the previous showable step

        for (var i = this.current_index - 1; i >= 0; i--) {
            if (this._isShowable(i)) {
                si = i;
                break;
            }
        }

        this._showTab(si);
    }

    private _isShowable(idx) {
        if (this.tabs.eq(idx).hasClass('disabled') || this.tabs.eq(idx).hasClass('hidden')) {
            return false;
        }

        return true;
    }

    private _showTab(idx) {
        // If current tab is requested again, skip
        if (idx == this.current_index) {
            return false;
        } // If tab not found, skip


        if (!this.tabs.eq(idx)) {
            return false;
        } // If it is a disabled tab, skip


        if (!this._isShowable(idx)) {
            return false;
        } // Load tab content


        this._loadTab(idx);
    }

    private _loadTab(idx) {
        var _this2 = this;

        // Get current tab element
        var curTab = this._getAnchor(this.current_index);

        if (this.current_index !== null) {
            // Trigger "leaveTab" event
            if (this._triggerEvent("leaveTab", [curTab, this.current_index]) === false) {
                return false;
            }
        } // Get next tab element


        var selTab = this._getAnchor(idx); // Get the content if used


        var getTabContent = this._triggerEvent("tabContent", [selTab, idx]);

        if (getTabContent) {
            if (_this2._typeof(getTabContent) == "object") {
                getTabContent.then(function (res) {
                    _this2._setTabContent(idx, res);

                    _this2._transitTab(idx);
                })["catch"](function (err) {
                    console.error(err);

                    _this2._setTabContent(idx, err);

                    _this2._transitTab(idx);
                });
            } else if (typeof getTabContent == "string") {
                this._setTabContent(idx, getTabContent);

                this._transitTab(idx);
            } else {
                this._transitTab(idx);
            }
        } else {
            this._transitTab(idx);
        }
    }

    private _getAnchor(idx) {
        if (idx == null) {
            return null;
        }

        return this.tabs.eq(idx);
    }

    private _getPage(idx) {
        if (idx == null) {
            return null;
        }

        var anchor = this._getAnchor(idx);

        return anchor.length > 0 ? this.main.find(anchor.attr("href")) : null;
    }

    private _setTabContent(idx, html) {
        var page = this._getPage(idx);

        if (page) {
            page.html(html);
        }
    }

    private _transitTab(idx) {
        var _this3 = this;

        // Get tab to show element
        var selTab = this._getAnchor(idx); // Change the url hash to new tab


        this._setURLHash(selTab.attr("href")); // Update controls


        this._setAnchor(idx); // Animate the tab


        this._doTabAnimation(idx, function () {
            // Fix height with content
            _this3._fixHeight(idx); // Trigger "showTab" event


            _this3._triggerEvent("showTab", [selTab, _this3.current_index]); // Restart auto progress if enabled


            _this3._restartAutoProgress();
        }); // Update the current index


        this.current_index = idx;
    }

    private _doTabAnimation(idx, callback) {
        var _this4 = this;

        // Get current tab element
        var curPage = this._getPage(this.current_index); // Get next tab element


        var selPage = this._getPage(idx); // Get the transition effect


        var transitionEffect = this.options.transition.animation.toLowerCase(); // Complete any ongoing animations

        this._stopAnimations();

        switch (transitionEffect) {
            case 'slide-horizontal':
            case 'slide-h':
                // horizontal slide
                var containerWidth = this.container.width();
                var curLastLeft = containerWidth;
                var nextFirstLeft = containerWidth * -2; // Forward direction

                if (idx > this.current_index) {
                    curLastLeft = containerWidth * -1;
                    nextFirstLeft = containerWidth;
                } // First load set the container width


                if (this.current_index == null) {
                    this.container.height(selPage.outerHeight());
                }

                var css_pos, css_left;

                if (curPage) {
                    css_pos = curPage.css("position");
                    css_left = curPage.css("left");
                    curPage.css("position", 'absolute').css("left", 0).animate({
                        left: curLastLeft
                    }, this.options.transition.speed, this.options.transition.easing, function () {
                        $(this).hide();
                        curPage.css("position", css_pos).css("left", css_left);
                    });
                }

                css_pos = selPage.css("position");
                css_left = selPage.css("left");
                selPage.css("position", 'absolute').css("left", nextFirstLeft).outerWidth(containerWidth).show().animate({
                    left: 0
                }, this.options.transition.speed, this.options.transition.easing, function () {
                    selPage.css("position", css_pos).css("left", css_left);
                    callback();
                });
                break;

            case 'slide-vertical':
            case 'slide-v':
                // vertical slide
                var containerHeight = this.container.height();
                var curLastTop = containerHeight;
                var nextFirstTop = containerHeight * -2; // Forward direction

                if (idx > this.current_index) {
                    curLastTop = containerHeight * -1;
                    nextFirstTop = containerHeight;
                }

                var css_vpos, css_vtop;

                if (curPage) {
                    css_vpos = curPage.css("position");
                    css_vtop = curPage.css("top");
                    curPage.css("position", 'absolute').css("top", 0).animate({
                        top: curLastTop
                    }, this.options.transition.speed, this.options.transition.easing, function () {
                        $(this).hide();
                        curPage.css("position", css_vpos).css("top", css_vtop);
                    });
                }

                css_vpos = selPage.css("position");
                css_vtop = selPage.css("top");
                selPage.css("position", 'absolute').css("top", nextFirstTop).show().animate({
                    top: 0
                }, this.options.transition.speed, this.options.transition.easing, function () {
                    selPage.css("position", css_vpos).css("top", css_vtop);
                    callback();
                });
                break;

            case 'slide-swing':
            case 'slide-s':
                // normal slide
                if (curPage) {
                    curPage.slideUp('fast', this.options.transition.easing, function () {
                        selPage.slideDown(_this4.options.transition.speed, _this4.options.transition.easing, function () {
                            callback();
                        });
                    });
                } else {
                    selPage.slideDown(this.options.transition.speed, this.options.transition.easing, function () {
                        callback();
                    });
                }

                break;

            case 'fade':
                // normal fade
                if (curPage) {
                    curPage.fadeOut('fast', this.options.transition.easing, function () {
                        selPage.fadeIn('fast', _this4.options.transition.easing, function () {
                            callback();
                        });
                    });
                } else {
                    selPage.fadeIn(this.options.transition.speed, this.options.transition.easing, function () {
                        callback();
                    });
                }

                break;

            default:
                if (curPage) {
                    curPage.hide();
                }

                selPage.show();
                callback();
                break;
        }
    }

    private _stopAnimations() {
        this.pages.finish();
        this.container.finish();
    };

    private _setAnchor(idx) {
        this.tabs.eq(this.current_index).removeClass("active");
        this.tabs.eq(idx).addClass("active");
    }

    private _getTabIndex() {
        // Get selected tab from the url
        var idx = this._getURLHashIndex();

        return idx === false ? this.options.selected : idx;
    }

    /**
     * @description here is the logic for handling animated height for wrapper - works good for modals,
     *              but the issue is that when modal is being hidden the panel height is initially 0
     */
    public _fixHeight(idx) {
        // volmarg - fix first tab not being active, add animation later on here

        // Auto adjust height of the container
        // if (this.options.autoAdjustHeight) {
        //     var selPage = this._getPage(idx);
        //     console.log(selPage.outerHeight());
        //
        //     this.container.finish().animate({
        //         height: selPage.outerHeight()
        //     }, this.options.transition.speed);
        // }
    }


    private _setTheme(theme) {
        this.main.removeClass(function (index, className) {
            return (className.match(/(^|\s)st-theme-\S+/g) || []).join(' ');
        }).addClass('st-theme-' + theme);
    }

    private _setOrientation(orientation) {
        this.main.removeClass('st-vertical st-horizontal').addClass('st-' + orientation);
    } // HELPER FUNCTIONS


    private _keyNav(e) {
        // Keyboard navigation
        if ($.inArray(e.which, this.options.keyboardSettings.keyLeft) > -1) {
            // left
            this._showPrevious();

            e.preventDefault();
        } else if ($.inArray(e.which, this.options.keyboardSettings.keyRight) > -1) {
            // right
            this._showNext();

            e.preventDefault();
        } else {
            return; // exit this handler for other keys
        }
    } // Auto progress

    private _startAutoProgress() {
        var _this5 = this;

        if (this.options.autoProgress.enabled && !this.autoProgressId) {
            this.autoProgressId = setInterval(function () {
                return _this5._showNext();
            }, this.options.autoProgress.interval);
        }
    }

    private _stopAutoProgress() {
        if (this.autoProgressId) {
            clearInterval(this.autoProgressId);
            this.autoProgressId = null;
        }
    }

    private _restartAutoProgress() {
        this._stopAutoProgress();

        this._startAutoProgress();
    }

    private _triggerEvent(name, params) {
        // Trigger an event
        var e = $.Event(name);
        this.main.trigger(e, params);

        if (e.isDefaultPrevented()) {
            return false;
        }

        //@ts-ignore
        return e.result;
    }

    private _setURLHash(hash) {
        if (this.options.enableURLhash && window.location.hash !== hash) {
            history.pushState(null, null, hash);
        }
    }

    private _getURLHashIndex() {
        if (this.options.enableURLhash) {
            // Get tab number from url hash if available
            var hash = window.location.hash;

            if (hash.length > 0) {
                var elm = this.nav.find("a[href*='" + hash + "']");

                if (elm.length > 0) {
                    return this.tabs.index(elm);
                }
            }
        }

        return false;
    }

    private _loader(action) {
        switch (action) {
            case 'show':
                this.main.addClass('st-loading');
                break;

            case 'hide':
                this.main.removeClass('st-loading');
                break;

            default:
                this.main.toggleClass('st-loading');
        }
    }

    private _showError(msg) {
        console.error(msg);
    } // PUBLIC FUNCTIONS

    private goToTab(tabIndex) {
        this._showTab(tabIndex);
    }

    public setOptions(options) {
        this.options = $.extend(true, {}, this.options, options);

        this._initOptions();
    }

    public loader(state) {
        if (state === "show") {
            this.main.addClass('st-loading');
        } else {
            this.main.removeClass('st-loading');
        }
    }

}