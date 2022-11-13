//PageBundle
Milex.pageOnLoad = function (container, response) {
    if (mQuery(container + ' #list-search').length) {
        Milex.activateSearchAutocomplete('list-search', 'page.page');
    }

    if (mQuery(container + ' #page_template').length) {
        Milex.toggleBuilderButton(mQuery('#page_template').val() == '');

        //Handle autohide of "Redirect URL" field if "Redirect Type" is none
        if (mQuery(container + ' select[name="page[redirectType]"]').length) {
            //Auto-hide on page loading
            Milex.autoHideRedirectUrl(container);

            //Auto-hide on select changing
            mQuery(container + ' select[name="page[redirectType]"]').chosen().change(function(){
                Milex.autoHideRedirectUrl(container);
            });
        }

        // Preload tokens for code mode builder
        Milex.getTokens(Milex.getBuilderTokensMethod(), function(){});
        Milex.initSelectTheme(mQuery('#page_template'));
    }

    // Open the builder directly when saved from the builder
    if (response && response.inBuilder) {
        Milex.launchBuilder('page');
        Milex.processBuilderErrors(response);
    }
};

Milex.getPageAbTestWinnerForm = function(abKey) {
    if (abKey && mQuery(abKey).val() && mQuery(abKey).closest('.form-group').hasClass('has-error')) {
        mQuery(abKey).closest('.form-group').removeClass('has-error');
        if (mQuery(abKey).next().hasClass('help-block')) {
            mQuery(abKey).next().remove();
        }
    }

    Milex.activateLabelLoadingIndicator('page_variantSettings_winnerCriteria');

    var pageId = mQuery('#page_sessionId').val();
    var query  = "action=page:getAbTestForm&abKey=" + mQuery(abKey).val() + "&pageId=" + pageId;

    mQuery.ajax({
        url: milexAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                if (mQuery('#page_variantSettings_properties').length) {
                    mQuery('#page_variantSettings_properties').replaceWith(response.html);
                } else {
                    mQuery('#page_variantSettings').append(response.html);
                }

                if (response.html != '') {
                    Milex.onPageLoad('#page_variantSettings_properties', response);
                }
            }

            Milex.removeLabelLoadingIndicator();

        },
        error: function (request, textStatus, errorThrown) {
            Milex.processAjaxError(request, textStatus, errorThrown);
            spinner.remove();
        },
        complete: function () {
            Milex.removeLabelLoadingIndicator();
        }
    });
};

Milex.autoHideRedirectUrl = function(container) {
    var select = mQuery(container + ' select[name="page[redirectType]"]');
    var input = mQuery(container + ' input[name="page[redirectUrl]"]');

    //If value is none we autohide the "Redirect URL" field and empty it
    if (select.val() == '') {
        input.closest('.form-group').hide();
        input.val('');
    } else {
        input.closest('.form-group').show();
    }
};