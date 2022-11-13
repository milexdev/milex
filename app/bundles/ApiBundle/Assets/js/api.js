//ApiBundle
Milex.clientOnLoad = function (container) {
    if (mQuery(container + ' #list-search').length) {
        Milex.activateSearchAutocomplete('list-search', 'api.client');
    }
};

Milex.refreshApiClientForm = function(url, modeEl) {
    var mode = mQuery(modeEl).val();

    if (mQuery('#client_redirectUris').length) {
        mQuery('#client_redirectUris').prop('disabled', true);
    } else {
        mQuery('#client_callback').prop('disabled', true);
    }
    mQuery('#client_name').prop('disabled', true);

    Milex.loadContent(url + '/' + mode);
};