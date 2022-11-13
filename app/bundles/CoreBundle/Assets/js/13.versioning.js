Milex.contentVersions = {};
Milex.versionNamespace = '';
Milex.currentContentVersion = -1;

/**
 * Setup versioning for the given namespace
 *
 * @param undoCallback function
 * @param redoCallback function
 * @param namespace
 */
Milex.prepareVersioning = function (undoCallback, redoCallback, namespace) {
    // Check if localStorage is supported and if not, disable undo/redo buttons
    if (!Milex.isLocalStorageSupported()) {
        mQuery('.btn-undo').prop('disabled', true);
        mQuery('.btn-redo').prop('disabled', true);

        return;
    }

    mQuery('.btn-undo')
        .prop('disabled', false)
        .on('click', function() {
            Milex.undoVersion(undoCallback);
        });

    mQuery('.btn-redo')
        .prop('disabled', false)
        .on('click', function() {
            Milex.redoVersion(redoCallback);
        });

    Milex.currentContentVersion = -1;

    if (!namespace) {
        namespace = window.location.href;
    }

    if (typeof Milex.contentVersions[namespace] == 'undefined') {
        Milex.contentVersions[namespace] = [];
    }

    Milex.versionNamespace = namespace;

    console.log(namespace);
};

/**
 * Clear versioning
 *
 * @param namespace
 */
Milex.clearVersioning = function () {
    if (!Milex.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (typeof Milex.contentVersions[Milex.versionNamespace] !== 'undefined') {
        delete Milex.contentVersions[Milex.versionNamespace];
    }

    Milex.versionNamespace = '';
    Milex.currentContentVersion = -1;
};

/**
 * Store a version
 *
 * @param content
 */
Milex.storeVersion = function(content) {
    if (!Milex.versionNamespace) {
        throw 'Versioning not configured';
    }

    // Store the content
    Milex.contentVersions[Milex.versionNamespace].push(content);

    // Set the current location to the latest spot
    Milex.currentContentVersion = Milex.contentVersions[Milex.versionNamespace].length;
};

/**
 * Decrement a version
 *
 * @param callback
 */
Milex.undoVersion = function(callback) {
    console.log('undo');
    if (!Milex.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (Milex.currentContentVersion < 0) {
        // Nothing to undo

        return;
    }

    var version = Milex.currentContentVersion - 1;
    if (Milex.getVersion(version, callback)) {
        --Milex.currentContentVersion;
    };
};

/**
 * Increment a version
 *
 * @param callback
 */
Milex.redoVersion = function(callback) {
    console.log('redo');
    if (!Milex.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (Milex.currentContentVersion < 0 || Milex.contentVersions[Milex.versionNamespace].length === Milex.currentContentVersion) {
        // Nothing to redo

        return;
    }

    var version = Milex.currentContentVersion + 1;
    if (Milex.getVersion(version, callback)) {
        ++Milex.currentContentVersion;
    };
};

/**
 * Check for a given version and execute callback
 *
 * @param version
 * @param command
 * @returns {boolean}
 */
Milex.getVersion = function(version, callback) {
    var content = false;
    if (typeof Milex.contentVersions[Milex.versionNamespace][version] !== 'undefined') {
        content = Milex.contentVersions[Milex.versionNamespace][version];
    }

    if (false !== content && typeof callback == 'function') {
        callback(content);

        return true;
    }

    return false;
};