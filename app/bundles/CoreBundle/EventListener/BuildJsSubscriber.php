<?php

namespace Milex\CoreBundle\EventListener;

use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event\BuildJsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BuildJsSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::BUILD_MILEX_JS => ['onBuildJs', 1000],
        ];
    }

    /**
     * Adds the MilexJS definition and core
     * JS functions for use in Bundles. This
     * must retain top priority of 1000.
     */
    public function onBuildJs(BuildJsEvent $event)
    {
        $js = <<<'JS'
// Polyfill for CustomEvent to support IE 9+
(function () {
    if ( typeof window.CustomEvent === "function" ) return false;
    function CustomEvent ( event, params ) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        var evt = document.createEvent( 'CustomEvent' );
        evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
        return evt;
    }
    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent;
})();

var MilexJS = MilexJS || {};

MilexJS.serialize = function(obj) {
    if ('string' == typeof obj) {
        return obj;
    }

    return Object.keys(obj).map(function(key) {
        return encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]);
    }).join('&');
};

MilexJS.documentReady = function(f) {
    /in/.test(document.readyState) ? setTimeout(function(){MilexJS.documentReady(f)}, 9) : f();
};

MilexJS.iterateCollection = function(collection) {
    return function(f) {
        for (var i = 0; collection[i]; i++) {
            f(collection[i], i);
        }
    };
};

MilexJS.log = function() {
    var log = {};
    log.history = log.history || [];

    log.history.push(arguments);

    if (window.console) {
        console.log(Array.prototype.slice.call(arguments));
    }
};

MilexJS.setCookie = function(name, value) {
    document.cookie = name+"="+value+"; path=/; secure";
};

MilexJS.createCORSRequest = function(method, url) {
    var xhr = new XMLHttpRequest();
    
    method = method.toUpperCase();
    
    if ("withCredentials" in xhr) {
        xhr.open(method, url, true);
    } else if (typeof XDomainRequest != "undefined") {
        xhr = new XDomainRequest();
        xhr.open(method, url);
    }
    
    return xhr;
};
MilexJS.CORSRequestsAllowed = true;
MilexJS.makeCORSRequest = function(method, url, data, callbackSuccess, callbackError) {
    // Check for stored contact in localStorage
    data = MilexJS.appendTrackedContact(data);
    
    var query = MilexJS.serialize(data);
    if (method.toUpperCase() === 'GET') {
        url = url + '?' + query;
        var query = '';
    }
    
    var xhr = MilexJS.createCORSRequest(method, url);
    var response;
    
    callbackSuccess = callbackSuccess || function(response, xhr) { };
    callbackError = callbackError || function(response, xhr) { };

    if (!xhr) {
        MilexJS.log('MilexJS.debug: Could not create an XMLHttpRequest instance.');
        return false;
    }

    if (!MilexJS.CORSRequestsAllowed) {
        callbackError({}, xhr);
        
        return false;
    }
    
    xhr.onreadystatechange = function (e) {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            response = MilexJS.parseTextToJSON(xhr.responseText);
            if (xhr.status === 200) {
                callbackSuccess(response, xhr);
            } else {
                callbackError(response, xhr);
               
                if (xhr.status === XMLHttpRequest.UNSENT) {
                    // Don't bother with further attempts
                    MilexJS.CORSRequestsAllowed = false;
                }
            }
        }
    };
   
    if (typeof xhr.setRequestHeader !== "undefined"){
        if (method.toUpperCase() === 'POST') {
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        }
    
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.withCredentials = true;
    }
    xhr.send(query);
};

MilexJS.parseTextToJSON = function(maybeJSON) {
    var response;

    try {
        // handle JSON data being returned
        response = JSON.parse(maybeJSON);
    } catch (error) {
        response = maybeJSON;
    }

    return response;
};

MilexJS.insertScript = function (scriptUrl) {
    var scriptsInHead = document.getElementsByTagName('head')[0].getElementsByTagName('script');
    var lastScript    = scriptsInHead[scriptsInHead.length - 1];
    var scriptTag     = document.createElement('script');
    scriptTag.async   = 1;
    scriptTag.src     = scriptUrl;
    
    if (lastScript) {
        lastScript.parentNode.insertBefore(scriptTag, lastScript);
    } else {
        document.getElementsByTagName('head')[0].appendChild(scriptTag);
    }
};

MilexJS.insertStyle = function (styleUrl) {
    var linksInHead = document.getElementsByTagName('head')[0].getElementsByTagName('link');
    var lastLink    = linksInHead[linksInHead.length - 1];
    var linkTag     = document.createElement('link');
    linkTag.rel     = "stylesheet";
    linkTag.type    = "text/css";
    linkTag.href    = styleUrl;
    
    if (lastLink) {
        lastLink.parentNode.insertBefore(linkTag, lastLink.nextSibling);
    } else {
        document.getElementsByTagName('head')[0].appendChild(linkTag);
    }
};

MilexJS.guid = function () {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    }
    
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
};

MilexJS.dispatchEvent = function(name, detail) {
    var event = new CustomEvent(name, {detail: detail});
    document.dispatchEvent(event);
};

function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
    .toString(16)
    .substring(1);
}

MilexJS.mtcSet = false;
MilexJS.appendTrackedContact = function(data) {
    if (window.localStorage) {
        if (mtcId  = localStorage.getItem('mtc_id')) {
            data['milex_device_id'] = localStorage.getItem('milex_device_id');
        }              
    }
    
    return data;
};

MilexJS.getTrackedContact = function () {
    if (MilexJS.mtcSet) {
        // Already set
        return;
    }
    
    MilexJS.makeCORSRequest('GET', MilexJS.contactIdUrl, {}, function(response, xhr) {
        MilexJS.setTrackedContact(response);
    });
};

MilexJS.setTrackedContact = function(response) {
    if (response.id) {
        MilexJS.setCookie('mtc_id', response.id);
        MilexJS.setCookie('mtc_sid', response.sid);
        MilexJS.setCookie('milex_device_id', response.device_id);
        MilexJS.mtcSet = true;
            
        // Set the id in local storage in case cookies are only allowed for sites visited and Milex is on a different domain
        // than the current page
        try {
            localStorage.setItem('mtc_id', response.id);
            localStorage.setItem('mtc_sid', response.sid);
            localStorage.setItem('milex_device_id', response.device_id);
        } catch (e) {
            console.warn('Browser does not allow storing in local storage');
        }
    }
};

// Register events that should happen after the first event is delivered
MilexJS.postEventDeliveryQueue = [];
MilexJS.firstDeliveryMade      = false;
MilexJS.onFirstEventDelivery = function(f) {
    MilexJS.postEventDeliveryQueue.push(f);
};
MilexJS.preEventDeliveryQueue = [];
MilexJS.beforeFirstDeliveryMade = false;
MilexJS.beforeFirstEventDelivery = function(f) {
    MilexJS.preEventDeliveryQueue.push(f);
};
document.addEventListener('milexPageEventDelivered', function(e) {
    var detail   = e.detail;
    var isImage = detail.image;
    if (isImage && !MilexJS.mtcSet) {
        MilexJS.getTrackedContact();
    } else if (detail.response && detail.response.id) {
        MilexJS.setTrackedContact(detail.response);
    }
    
    if (!isImage && typeof detail.event[3] === 'object' && typeof detail.event[3].onload === 'function') {
       // Execute onload since this is ignored if not an image
       detail.event[3].onload(detail)       
    }
    
    if (!MilexJS.firstDeliveryMade) {
        MilexJS.firstDeliveryMade = true;
        for (var i = 0; i < MilexJS.postEventDeliveryQueue.length; i++) {
            if (typeof MilexJS.postEventDeliveryQueue[i] === 'function') {
                MilexJS.postEventDeliveryQueue[i](detail);
            }
            delete MilexJS.postEventDeliveryQueue[i];
        }
    }
});

/**
* Check if a DOM tracking pixel is present
*/
MilexJS.checkForTrackingPixel = function() {
    if (document.readyState !== 'complete') {
        // Periodically call self until the DOM is completely loaded
        setTimeout(function(){MilexJS.checkForTrackingPixel()}, 9)
    } else {
        // Only fetch once a tracking pixel has been loaded
        var maxChecks  = 3000; // Keep it from indefinitely checking in case the pixel was never embedded
        var checkPixel = setInterval(function() {
            if (maxChecks > 0 && !MilexJS.isPixelLoaded(true)) {
                // Try again
                maxChecks--;
                return;
            }
    
            clearInterval(checkPixel);
            
            if (maxChecks > 0) {
                // DOM image was found 
                var params = {}, hash;
                var hashes = MilexJS.trackingPixel.src.slice(MilexJS.trackingPixel.src.indexOf('?') + 1).split('&');

                for(var i = 0; i < hashes.length; i++) {
                    hash = hashes[i].split('=');
                    params[hash[0]] = hash[1];
                }

                MilexJS.dispatchEvent('milexPageEventDelivered', {'event': ['send', 'pageview', params], 'params': params, 'image': true});
            }
        }, 1);
    }
}
MilexJS.checkForTrackingPixel();

MilexJS.isPixelLoaded = function(domOnly) {
    if (typeof domOnly == 'undefined') {
        domOnly = false;
    }
    
    if (typeof MilexJS.trackingPixel === 'undefined') {
        // Check the DOM for the tracking pixel
        MilexJS.trackingPixel = null;
        var imgs = Array.prototype.slice.apply(document.getElementsByTagName('img'));
        for (var i = 0; i < imgs.length; i++) {
            if (imgs[i].src.indexOf('mtracking.gif') !== -1) {
                MilexJS.trackingPixel = imgs[i];
                break;
            }
        }
    } else if (domOnly) {
        return false;
    }

    if (MilexJS.trackingPixel && MilexJS.trackingPixel.complete && MilexJS.trackingPixel.naturalWidth !== 0) {
        // All the browsers should be covered by this - image is loaded
        return true;
    }

    return false;
};

if (typeof window[window.MilexTrackingObject] !== 'undefined') {
    MilexJS.input = window[window.MilexTrackingObject];
    if (typeof MilexJS.input.q === 'undefined') {
        // In case mt() is not executed right away
        MilexJS.input.q = [];
    }
    MilexJS.inputQueue = MilexJS.input.q;

    // Dispatch the queue event when an event is added to the queue
    if (!MilexJS.inputQueue.hasOwnProperty('push')) {
        Object.defineProperty(MilexJS.inputQueue, 'push', {
            configurable: false,
            enumerable: false,
            writable: false,
            value: function () {
                for (var i = 0, n = this.length, l = arguments.length; i < l; i++, n++) {
                    MilexJS.dispatchEvent('eventAddedToMilexQueue', arguments[i]);
                }
                return n;
            }
        });
    }

    MilexJS.getInput = function(task, type) {
        var matches = [];
        if (typeof MilexJS.inputQueue !== 'undefined' && MilexJS.inputQueue.length) {
            for (var i in MilexJS.inputQueue) {
                if (MilexJS.inputQueue[i][0] === task && MilexJS.inputQueue[i][1] === type) {
                    matches.push(MilexJS.inputQueue[i]);
                }
            }
        }
        
        return matches; 
    }
}

MilexJS.ensureEventContext = function(event, context0, context1) { 
    return (typeof(event.detail) !== 'undefined'
        && event.detail[0] === context0
        && event.detail[1] === context1);
};
JS;
        $event->appendJs($js, 'Milex Core');
    }
}
