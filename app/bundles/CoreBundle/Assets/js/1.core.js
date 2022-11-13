var MilexVars  = {};
var mQuery      = jQuery.noConflict(true);
window.jQuery   = mQuery;

// Polyfil for ES6 startsWith method
if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position){
        position = position || 0;
        return this.substr(position, searchString.length) === searchString;
    };
}

//set default ajax options
MilexVars.activeRequests = 0;

mQuery.ajaxSetup({
    beforeSend: function (request, settings) {
        if (settings.showLoadingBar) {
            Milex.startPageLoadingBar();
        }

        if (typeof IdleTimer != 'undefined') {
            //append last active time
            var userLastActive = IdleTimer.getLastActive();
            var queryGlue = (settings.url.indexOf("?") == -1) ? '?' : '&';

            settings.url = settings.url + queryGlue + 'milexUserLastActive=' + userLastActive;
        }

        if (mQuery('#milexLastNotificationId').length) {
            //append last notifications
            var queryGlue = (settings.url.indexOf("?") == -1) ? '?' : '&';

            settings.url = settings.url + queryGlue + 'milexLastNotificationId=' + mQuery('#milexLastNotificationId').val();
        }

        // Set CSRF token to each AJAX POST request
        if (settings.type == 'POST') {
            request.setRequestHeader('X-CSRF-Token', milexAjaxCsrf);
        }

        return true;
    },

    cache: false
});

mQuery( document ).ajaxComplete(function(event, xhr, settings) {
    Milex.stopPageLoadingBar();
    if (xhr.responseJSON && xhr.responseJSON.flashes) {
        Milex.setFlashes(xhr.responseJSON.flashes);
    }
});

// Force stop the page loading bar when no more requests are being in progress
mQuery( document ).ajaxStop(function(event) {
    // Seems to be stuck
    MilexVars.activeRequests = 0;
    Milex.stopPageLoadingBar();
});

mQuery( document ).ready(function() {
    if (typeof milexContent !== 'undefined') {
        mQuery("html").Core({
            console: false
        });
    }

    // Prevent backspace from activating browser back
    mQuery(document).on('keydown', function (e) {
        if (e.which === 8 && !mQuery(e.target).is("input:not([readonly]):not([type=radio]):not([type=checkbox]), textarea, [contentEditable], [contentEditable=true]")) {
            e.preventDefault();
        }
    });
});

//Fix for back/forward buttons not loading ajax content with History.pushState()
MilexVars.manualStateChange = true;

if (typeof History != 'undefined') {
    History.Adapter.bind(window, 'statechange', function () {
        if (MilexVars.manualStateChange == true) {
            //back/forward button pressed
            window.location.reload();
        }
        MilexVars.manualStateChange = true;
    });
}

//used for spinning icons to show something is in progress)
MilexVars.iconClasses          = {};

//prevent multiple ajax calls from multiple clicks
MilexVars.routeInProgress       = '';

//prevent interval ajax requests from overlapping
MilexVars.moderatedIntervals    = {};
MilexVars.intervalsInProgress   = {};

var Milex = {
    loadedContent: {},

    keyboardShortcutHtml: {},

    /**
     *
     * @param sequence
     * @param description
     * @param func
     * @param section
     */
    addKeyboardShortcut: function (sequence, description, func, section) {
        Mousetrap.bind(sequence, func);
        var sectionName = section || 'global';

        if (!Milex.keyboardShortcutHtml.hasOwnProperty(sectionName)) {
            Milex.keyboardShortcutHtml[sectionName] = {};
        }

        Milex.keyboardShortcutHtml[sectionName][sequence] = '<div class="col-xs-6"><mark>' + sequence + '</mark>: ' + description + '</div>';
    },

    /**
     * Binds global keyboard shortcuts
     */
    bindGlobalKeyboardShortcuts: function () {
        Milex.addKeyboardShortcut('shift+d', 'Load the Dashboard', function (e) {
            mQuery('#milex_dashboard_index').click();
        });

        Milex.addKeyboardShortcut('shift+c', 'Load Contacts', function (e) {
            mQuery('#milex_contact_index').click();
        });

        Milex.addKeyboardShortcut('shift+right', 'Activate Right Menu', function (e) {
            mQuery(".navbar-right a[data-toggle='sidebar']").click();
        });

        Milex.addKeyboardShortcut('shift+n', 'Show Notifications', function (e) {
            mQuery('.dropdown-notification').click();
        });

        Milex.addKeyboardShortcut('shift+s', 'Global Search', function (e) {
            mQuery('#globalSearchContainer .search-button').click();
        });

        Milex.addKeyboardShortcut('mod+z', 'Undo change', function (e) {
            if (mQuery('.btn-undo').length) {
                mQuery('.btn-undo').click();
            }
        });

        Milex.addKeyboardShortcut('mod+shift+z', 'Redo change', function (e) {
            if (mQuery('.btn-redo').length) {
                mQuery('.btn-redo').click();
            }
        });

        Mousetrap.bind('?', function (e) {
            var modalWindow = mQuery('#MilexSharedModal');

            modalWindow.find('.modal-title').html('Keyboard Shortcuts');
            modalWindow.find('.modal-body').html(function () {
                var modalHtml = '';
                var sections = Object.keys(Milex.keyboardShortcutHtml);
                sections.forEach(function (section) {
                    var sectionTitle = (section + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
                        return $1.toUpperCase();
                    });
                    modalHtml += '<h4>' + sectionTitle + '</h4><br />';
                    modalHtml += '<div class="row">';
                    var sequences = Object.keys(Milex.keyboardShortcutHtml[section]);
                    sequences.forEach(function (sequence) {
                        modalHtml += Milex.keyboardShortcutHtml[section][sequence];
                    });
                    modalHtml += '</div><hr />';
                });

                return modalHtml;
            });
            modalWindow.find('.modal-footer').html('<p>Press <mark>shift+?</mark> at any time to view this help modal.');
            modalWindow.modal();
        });
    },

    /**
     * Translations
     *
     * @param id     string
     * @param params object
     */
    translate: function (id, params) {
        if (!milexLang.hasOwnProperty(id)) {
            return id;
        }

        var translated = milexLang[id];

        if (params) {
            for (var key in params) {
                if (!params.hasOwnProperty(key)) continue;

                var regEx = new RegExp('%' + key + '%', 'g');
                translated = translated.replace(regEx, params[key])
            }
        }

        return translated;
    },

    /**
     * Setups browser notifications
     */
    setupBrowserNotifier: function () {
        //request notification support
        notify.requestPermission();
        notify.config({
            autoClose: 10000
        });

        Milex.browserNotifier = {
            isSupported: notify.isSupported,
            permissionLevel: notify.permissionLevel()
        };

        Milex.browserNotifier.isSupported = notify.isSupported;
        Milex.browserNotifier.permissionLevel = notify.permissionLevel();
        Milex.browserNotifier.createNotification = function (title, options) {
            return notify.createNotification(title, options);
        }
    },

    /**
     * Stops the ajax page loading indicator
     */
    stopPageLoadingBar: function () {
        if (MilexVars.activeRequests < 1) {
            MilexVars.activeRequests = 0;
        } else {
            MilexVars.activeRequests--;
        }

        if (MilexVars.loadingBarTimeout) {
            clearTimeout(MilexVars.loadingBarTimeout);
        }

        if (MilexVars.activeRequests == 0) {
            mQuery('.loading-bar').removeClass('active');
        }
    },

    /**
     * Activate page loading bar
     */
    startPageLoadingBar: function () {
        mQuery('.loading-bar').addClass('active');
        MilexVars.activeRequests++;
    },

    /**
     * Starts the ajax loading indicator for the right canvas
     */
    startCanvasLoadingBar: function () {
        mQuery('.canvas-loading-bar').addClass('active');
    },

    /**
     * Starts the ajax loading indicator for modals
     *
     * @param modalTarget
     */
    startModalLoadingBar: function (modalTarget) {
        mQuery(modalTarget + ' .modal-loading-bar').addClass('active');
    },

    /**
     * Stops the ajax loading indicator for the right canvas
     */
    stopCanvasLoadingBar: function () {
        mQuery('.canvas-loading-bar').removeClass('active');
    },

    /**
     * Stops the ajax loading indicator for modals
     */
    stopModalLoadingBar: function (modalTarget) {
        mQuery(modalTarget + ' .modal-loading-bar').removeClass('active');
    },

    /**
     * Activate label loading spinner
     *
     * @param button (jQuery element)
     */
    activateButtonLoadingIndicator: function (button) {
        button.prop('disabled', true);
        if (!button.find('.fa-spinner.fa-spin').length) {
            button.append(mQuery('<i class="fa fa-fw fa-spinner fa-spin"></i>'));
        }
    },

    /**
     * Remove the spinner from label
     *
     * @param button (jQuery element)
     */
    removeButtonLoadingIndicator: function (button) {
        button.prop('disabled', false);
        button.find('.fa-spinner').remove();
    },

    /**
     * Activate label loading spinner
     *
     * @param el
     */
    activateLabelLoadingIndicator: function (el) {
        var labelSpinner = mQuery("label[for='" + el + "']");
        Milex.labelSpinner = mQuery('<i class="fa fa-fw fa-spinner fa-spin"></i>');
        labelSpinner.append(Milex.labelSpinner);
    },

    /**
     * Remove the spinner from label
     */
    removeLabelLoadingIndicator: function () {
        mQuery(Milex.labelSpinner).remove();
    },

    /**
     * Open a popup
     * @param options
     */
    loadNewWindow: function (options) {
        if (options.windowUrl) {
            Milex.startModalLoadingBar();

            var popupName = 'milexpopup';
            if (options.popupName) {
                popupName = options.popupName;
            }

            setTimeout(function () {
                var opener = window.open(options.windowUrl, popupName, 'height=600,width=1100');

                if (!opener || opener.closed || typeof opener.closed == 'undefined') {
                    alert(milexLang.popupBlockerMessage);
                } else {
                    opener.onload = function () {
                        Milex.stopModalLoadingBar();
                        Milex.stopIconSpinPostEvent();
                    };
                }
            }, 100);
        }
    },

    /**
     * Inserts a new javascript file request into the document head
     *
     * @param url
     * @param onLoadCallback
     * @param alreadyLoadedCallback
     */
    loadScript: function (url, onLoadCallback, alreadyLoadedCallback) {
        // check if the asset has been loaded
        if (typeof Milex.headLoadedAssets == 'undefined') {
            Milex.headLoadedAssets = {};
        } else if (typeof Milex.headLoadedAssets[url] != 'undefined') {
            // URL has already been appended to head

            if (alreadyLoadedCallback && typeof Milex[alreadyLoadedCallback] == 'function') {
                Milex[alreadyLoadedCallback]();
            }

            return;
        }

        // Note that asset has been appended
        Milex.headLoadedAssets[url] = 1;

        mQuery.getScript(url, function (data, textStatus, jqxhr) {
            if (textStatus == 'success') {
                if (onLoadCallback && typeof Milex[onLoadCallback] == 'function') {
                    Milex[onLoadCallback]();
                } else if (typeof Milex[milexContent + "OnLoad"] == 'function') {
                    // Likely a page refresh; execute onLoad content
                    if (typeof Milex.loadedContent[milexContent] == 'undefined') {
                        Milex.loadedContent[milexContent] = true;
                        Milex[milexContent + "OnLoad"]('#app-content', {});
                    }
                }
            }
        });
    },

    /**
     * Inserts a new stylesheet into the document head
     *
     * @param url
     */
    loadStylesheet: function (url) {
        // check if the asset has been loaded
        if (typeof Milex.headLoadedAssets == 'undefined') {
            Milex.headLoadedAssets = {};
        } else if (typeof Milex.headLoadedAssets[url] != 'undefined') {
            // URL has already been appended to head
            return;
        }

        // Note that asset has been appended
        Milex.headLoadedAssets[url] = 1;

        var link = document.createElement("link");
        link.type = "text/css";
        link.rel = "stylesheet";
        link.href = url;
        mQuery('head').append(link);
    },

    /**
     * Just a little visual that an action is taking place
     *
     * @param event|string
     */
    startIconSpinOnEvent: function (target) {
        if (MilexVars.ignoreIconSpin) {
            MilexVars.ignoreIconSpin = false;
            return;
        }

        if (typeof target == 'object' && typeof(target.target) !== 'undefined') {
            target = target.target;
        }

        if (mQuery(target).length) {
            var hasBtn = mQuery(target).hasClass('btn');
            var hasIcon = mQuery(target).hasClass('fa');
            var dontspin = mQuery(target).hasClass('btn-nospin');

            var i = (hasBtn && mQuery(target).find('i.fa').length) ? mQuery(target).find('i.fa') : target;

            if (!dontspin && ((hasBtn && mQuery(target).find('i.fa').length) || hasIcon)) {
                var el = (hasIcon) ? target : mQuery(target).find('i.fa').first();
                var identifierClass = (new Date).getTime();
                MilexVars.iconClasses[identifierClass] = mQuery(el).attr('class');

                var specialClasses = ['fa-fw', 'fa-lg', 'fa-2x', 'fa-3x', 'fa-4x', 'fa-5x', 'fa-li', 'text-white', 'text-muted'];
                var appendClasses = "";

                //check for special classes to add to spinner
                for (var i = 0; i < specialClasses.length; i++) {
                    if (mQuery(el).hasClass(specialClasses[i])) {
                        appendClasses += " " + specialClasses[i];
                    }
                }
                mQuery(el).removeClass();
                mQuery(el).addClass('fa fa-spinner fa-spin ' + identifierClass + appendClasses);
            }
        }
    },

    /**
     * Stops the icon spinning after an event is complete
     */
    stopIconSpinPostEvent: function (specificId) {
        if (typeof specificId != 'undefined' && specificId in MilexVars.iconClasses) {
            mQuery('.' + specificId).removeClass('fa fa-spinner fa-spin ' + specificId).addClass(MilexVars.iconClasses[specificId]);
            delete MilexVars.iconClasses[specificId];
        } else {
            mQuery.each(MilexVars.iconClasses, function (index, value) {
                mQuery('.' + index).removeClass('fa fa-spinner fa-spin ' + index).addClass(value);
            });

            MilexVars.iconClasses = {};
        }
    },

    /**
     * Displays backdrop with wait message then redirects
     *
     * @param url
     */
    redirectWithBackdrop: function (url) {
        Milex.activateBackdrop();
        setTimeout(function () {
            window.location = url;
        }, 50);
    },

    /**
     * Acivates a backdrop
     */
    activateBackdrop: function (hideWait) {
        if (!mQuery('#milex-backdrop').length) {
            var container = mQuery('<div />', {
                id: 'milex-backdrop'
            });

            mQuery('<div />', {
                'class': 'modal-backdrop fade in'
            }).appendTo(container);

            if (typeof hideWait == 'undefined') {
                mQuery('<div />', {
                    "class": 'milex-pleasewait'
                }).html(milexLang.pleaseWait)
                    .appendTo(container);
            }

            container.appendTo('body');
        }
    },

    /**
     * Deactivates backdrop
     */
    deactivateBackgroup: function () {
        if (mQuery('#milex-backdrop').length) {
            mQuery('#milex-backdrop').remove();
        }
    },

    /**
     * Executes an object action
     *
     * @param action
     */
    executeAction: function (action, callback) {
        if (typeof Milex.activeActions == 'undefined') {
            Milex.activeActions = {};
        } else if (typeof Milex.activeActions[action] != 'undefined') {
            // Action is currently being executed
            return;
        }

        Milex.activeActions[action] = true;

        //dismiss modal if activated
        Milex.dismissConfirmation();

        if (action.indexOf('batchExport') >= 0) {
            Milex.initiateFileDownload(action);
            return;
        }

        mQuery.ajax({
            showLoadingBar: true,
            url: action,
            type: "POST",
            dataType: "json",
            success: function (response) {
                Milex.processPageContent(response);

                if (typeof callback == 'function') {
                    callback(response);
                }
            },
            error: function (request, textStatus, errorThrown) {
                Milex.processAjaxError(request, textStatus, errorThrown);
            },
            complete: function () {
                delete Milex.activeActions[action]
            }
        });
    },

    /**
     * Processes ajax errors
     *
     *
     * @param request
     * @param textStatus
     * @param errorThrown
     */
    processAjaxError: function (request, textStatus, errorThrown, mainContent) {
        if (textStatus == 'abort') {
            Milex.stopPageLoadingBar();
            Milex.stopCanvasLoadingBar();
            Milex.stopIconSpinPostEvent();
            return;
        }

        var inDevMode = typeof milexEnv !== 'undefined' && milexEnv == 'dev';

        if (inDevMode) {
            console.log(request);
        }

        if (typeof request.responseJSON !== 'undefined') {
            response = request.responseJSON;
        } else if (typeof(request.responseText) !== 'undefined') {
            //Symfony may have added some excess buffer if an exception was hit during a sub rendering and because
            //it uses ob_start, PHP dumps the buffer upon hitting the exception.  So let's filter that out.
            var errorStart = request.responseText.indexOf('{"newContent');
            var jsonString = request.responseText.slice(errorStart);

            if (jsonString) {
                try {
                    var response = JSON.parse(jsonString);
                    if (inDevMode) {
                        console.log(response);
                    }
                } catch (err) {
                    if (inDevMode) {
                        console.log(err);
                    }
                }
            } else {
                response = {};
            }
        }

        if (response) {
            if (response.newContent && mainContent) {
                //an error page was returned
                mQuery('#app-content .content-body').html(response.newContent);
                if (response.route && response.route.indexOf("ajax") == -1) {
                    //update URL in address bar
                    MilexVars.manualStateChange = false;
                    History.pushState(null, "Milex", response.route);
                }
            } else if (response.newContent && mQuery('.modal.in').length) {
                //assume a modal was the recipient of the information
                mQuery('.modal.in .modal-body-content').html(response.newContent);
                mQuery('.modal.in .modal-body-content').removeClass('hide');
                if (mQuery('.modal.in  .loading-placeholder').length) {
                    mQuery('.modal.in  .loading-placeholder').addClass('hide');
                }
            } else if (inDevMode) {
                console.log(response);

                if (response.errors && response.errors[0] && response.errors[0].message) {
                    alert(response.errors[0].message);
                }
            }
        }

        Milex.stopPageLoadingBar();
        Milex.stopCanvasLoadingBar();
        Milex.stopIconSpinPostEvent();
    },

    /**
     * Moderates intervals to prevent ajax overlaps
     *
     * @param key
     * @param callback
     * @param timeout
     */
    setModeratedInterval: function (key, callback, timeout, params) {
        if (typeof MilexVars.intervalsInProgress[key] != 'undefined') {
            //action is still pending so clear and reschedule
            clearTimeout(MilexVars.moderatedIntervals[key]);
        } else {
            MilexVars.intervalsInProgress[key] = true;

            //perform callback
            if (typeof params == 'undefined') {
                params = [];
            }

            if (typeof callback == 'function') {
                callback(params);
            } else {
                window["Milex"][callback].apply('window', params);
            }
        }

        //schedule new timeout
        MilexVars.moderatedIntervals[key] = setTimeout(function () {
            Milex.setModeratedInterval(key, callback, timeout, params)
        }, timeout);
    },

    /**
     * Call at the end of the moderated interval callback function to let setModeratedInterval know
     * the action is done and it's safe to execute again
     *
     * @param key
     */
    moderatedIntervalCallbackIsComplete: function (key) {
        delete MilexVars.intervalsInProgress[key];
    },

    /**
     * Clears a moderated interval
     *
     * @param key
     */
    clearModeratedInterval: function (key) {
        Milex.moderatedIntervalCallbackIsComplete(key);
        clearTimeout(MilexVars.moderatedIntervals[key]);
        delete MilexVars.moderatedIntervals[key];
    },

    /**
     * Sets flashes
     * @param flashes
     */
    setFlashes: function (flashes) {
        mQuery('#flashes').append(flashes);

        mQuery('#flashes .alert-new').each(function () {
            var me = this;
            window.setTimeout(function () {
                mQuery(me).fadeTo(500, 0).slideUp(500, function () {
                    mQuery(this).remove();
                });
            }, 4000);

            mQuery(this).removeClass('alert-new');
        });
    },

    /**
     * Set browser notifications
     *
     * @param notifications
     */
    setBrowserNotifications: function (notifications) {
        mQuery.each(notifications, function (key, notification) {
            Milex.browserNotifier.createNotification(
                notification.title,
                {
                    body: notification.message,
                    icon: notification.icon
                }
            );
        });
    },

    /**
     *
     * @param notifications
     */
    setNotifications: function (notifications) {
        if (notifications.lastId) {
            mQuery('#milexLastNotificationId').val(notifications.lastId);
        }

        if (mQuery('#notifications .milex-update')) {
            mQuery('#notifications .milex-update').remove();
        }

        if (notifications.hasNewNotifications) {
            if (mQuery('#newNotificationIndicator').hasClass('hide')) {
                mQuery('#newNotificationIndicator').removeClass('hide');
            }
        }

        if (notifications.content) {
            mQuery('#notifications').prepend(notifications.content);

            if (!mQuery('#notificationMautibot').hasClass('hide')) {
                mQuery('#notificationMautibot').addClass('hide');
            }
        }

        if (notifications.sound) {
            mQuery('.playSound').remove();

            mQuery.playSound(notifications.sound);
        }
    },

    /**
     * Marks notifications as read and clears unread indicators
     */
    showNotifications: function () {
        mQuery("#notificationsDropdown").off('hide.bs.dropdown');
        mQuery('#notificationsDropdown').on('hidden.bs.dropdown', function () {
            if (!mQuery('#newNotificationIndicator').hasClass('hide')) {
                mQuery('#notifications .is-unread').remove();
                mQuery('#newNotificationIndicator').addClass('hide');
            }
        });
    },

    /**
     * Clear notification(s)
     * @param id
     */
    clearNotification: function (id) {
        if (id) {
            mQuery("#notification" + id).fadeTo("fast", 0.01).slideUp("fast", function () {
                mQuery(this).find("*[data-toggle='tooltip']").tooltip('destroy');
                mQuery(this).remove();

                if (!mQuery('#notifications .notification').length) {
                    if (mQuery('#notificationMautibot').hasClass('hide')) {
                        mQuery('#notificationMautibot').removeClass('hide');
                    }
                }
            });
        } else {
            mQuery("#notifications .notification").fadeOut(300, function () {
                mQuery(this).remove();

                if (mQuery('#notificationMautibot').hasClass('hide')) {
                    mQuery('#notificationMautibot').removeClass('hide');
                }
            });
        }

        mQuery.ajax({
            url: milexAjaxUrl,
            type: "GET",
            data: "action=clearNotification&id=" + id
        });
    },

    /**
     * Execute an action to AjaxController
     *
     * @param action
     * @param data
     * @param successClosure
     * @param showLoadingBar
     * @param queue
     * @param method
     */
    ajaxActionRequest: function (action, data, successClosure, showLoadingBar, queue, method = "POST") {
        if (typeof Milex.ajaxActionXhrQueue == 'undefined') {
            Milex.ajaxActionXhrQueue = {};
        }
        if (typeof Milex.ajaxActionXhr == 'undefined') {
            Milex.ajaxActionXhr = {};
        } else if (typeof Milex.ajaxActionXhr[action] != 'undefined') {
            if (queue) {
                if (typeof Milex.ajaxActionXhrQueue[action] == 'undefined') {
                    Milex.ajaxActionXhrQueue[action] = [];
                }

                Milex.ajaxActionXhrQueue[action].push({action: action, data: data, successClosure: successClosure, showLoadingBar: showLoadingBar, method: method});

                return;
            } else {
                Milex.removeLabelLoadingIndicator();
                Milex.ajaxActionXhr[action].abort();
            }
        }

        if (typeof showLoadingBar == 'undefined') {
            showLoadingBar = false;
        }

        Milex.ajaxActionXhr[action] = mQuery.ajax({
            url: milexAjaxUrl + '?action=' + action,
            type: method,
            data: data,
            showLoadingBar: showLoadingBar,
            success: function (response) {
                if (typeof successClosure == 'function') {
                    successClosure(response);
                }
            },
            error: function (request, textStatus, errorThrown) {
                Milex.processAjaxError(request, textStatus, errorThrown, true);
            },
            complete: function () {
                delete Milex.ajaxActionXhr[action];

                if (typeof Milex.ajaxActionXhrQueue[action] !== 'undefined' && Milex.ajaxActionXhrQueue[action].length) {
                    var next = Milex.ajaxActionXhrQueue[action].shift();

                    Milex.ajaxActionRequest(next.action, next.data, next.successClosure, next.showLoadingBar, false, next.method);
                }
            }
        });
    },

    /**
     * Check if the browser supports local storage
     *
     * @returns {boolean}
     */
    isLocalStorageSupported: function() {
        try {
            // Check if localStorage is supported
            localStorage.setItem('milex.test', 'milex');
            localStorage.removeItem('milex.test');

            return true;
        } catch (e) {
            return false;
        }
    }
};