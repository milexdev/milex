Milex.integrationsConfigOnLoad = function () {
    mQuery('.integration-keyword-filter').each(function() {
        mQuery(this).off("keydown.integration-filter").on("keydown.integration-filter", function (event) {
            if (event.which == 13) {
                var integration = mQuery(this).attr('data-integration');
                var object = mQuery(this).attr('data-object');
                Milex.getPaginatedIntegrationFields(
                    {
                        'integration': integration,
                        'object': object,
                        'keyword': mQuery(this).val()
                    },
                    1,
                    this
                );
            }
        });
    });

    Milex.activateIntegrationFieldUpdateActions();
};

Milex.getPaginatedIntegrationFields = function(settings, page, element) {
    var requestName = settings.integration + '-' + settings.object;
    var action = milexBaseUrl + 's/integration/' + settings.integration + '/config/' + settings.object + '/' + page;
    if (settings.keyword) {
        action = action + '?keyword=' + settings.keyword;
    }

    if (typeof Milex.activeActions == 'undefined') {
        Milex.activeActions = {};
    } else if (typeof Milex.activeActions[requestName] != 'undefined') {
        Milex.activeActions[requestName].abort();
    }

    var object    = settings.object;
    var fieldsTab = '#field-mappings-'+object+'-container';

    if (element && mQuery(element).is('input')) {
        Milex.activateLabelLoadingIndicator(mQuery(element).attr('id'));
    }
    var fieldsContainer = '#field-mappings-'+object;

    var modalId = '#'+mQuery(fieldsContainer).closest('.modal').attr('id');
    Milex.startModalLoadingBar(modalId);

    Milex.activeActions[requestName] = mQuery.ajax({
        showLoadingBar: false,
        url: action,
        type: "POST",
        dataType: "json",
        success: function (response) {
            if (response.success) {
                mQuery(fieldsContainer).html(response.html);
                Milex.onPageLoad(fieldsContainer);
                Milex.activateIntegrationFieldUpdateActions();
                if (mQuery(fieldsTab).length) {
                    mQuery(fieldsTab).removeClass('hide');
                }
            } else if (mQuery(fieldsTab).length) {
                mQuery(fieldsTab).addClass('hide');
            }

            if (element) {
                Milex.removeLabelLoadingIndicator();
            }

            Milex.stopModalLoadingBar(modalId);
        },
        error: function (request, textStatus, errorThrown) {
            Milex.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function () {
            delete Milex.activeActions[requestName]
        }
    });
};

Milex.updateIntegrationField = function(integration, object, field, fieldOption, fieldValue) {
    var action = milexBaseUrl + 's/integration/' + integration + '/config/' + object + '/field/' + field;
    var modal = mQuery('form[name=integration_config]').closest('.modal');
    var requestName = integration + object + field + fieldOption;

    // Disable submit buttons until the action is done so nothing is lost
    mQuery(modal).find('.modal-form-buttons .btn').prop('disabled', true);

    if (typeof Milex.activeActions == 'undefined') {
        Milex.activeActions = {};
    } else if (typeof Milex.activeActions[requestName] != 'undefined') {
        Milex.activeActions[requestName].abort();
    }

    Milex.startModalLoadingBar(mQuery(modal).attr('id'));

    // Must use bracket notation to use variable for key
    var obj = {};
    obj[fieldOption] = fieldValue;

    Milex.activeActions[requestName] = mQuery.ajax({
        showLoadingBar: false,
        url: action,
        type: "POST",
        dataType: "json",
        data: obj,
        error: function (request, textStatus, errorThrown) {
            Milex.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function () {
            modal.find('.modal-form-buttons .btn').prop('disabled', false);
            delete Milex.activeActions[requestName];
        }
    });
};

Milex.activateIntegrationFieldUpdateActions = function () {
    mQuery('.integration-mapped-field').each(function() {
        mQuery(this).off("change.integration-mapped-field").on("change.integration-mapped-field", function (event) {
            var integration = mQuery(this).attr('data-integration');
            var object = mQuery(this).attr('data-object');
            var field = mQuery(this).attr('data-field');
            Milex.updateIntegrationField(integration, object, field, 'mappedField', mQuery(this).val());
        });
    });

    mQuery('.integration-sync-direction').each(function() {
        mQuery(this).off("change.integration-sync-direction").on("change.integration-sync-direction", function (event) {
            var integration = mQuery(this).attr('data-integration');
            var object = mQuery(this).attr('data-object');
            var field = mQuery(this).attr('data-field');
            Milex.updateIntegrationField(integration, object, field, 'syncDirection', mQuery(this).val());
        });
    });
};

Milex.authorizeIntegration = function () {
    mQuery('#integration_details_in_auth').val(1);
    Milex.postForm(mQuery('form[name="integration_config"]'), 'loadIntegrationAuthWindow');
};