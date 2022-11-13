//FormBundle
Milex.formOnLoad = function (container) {

    if (mQuery(container + ' #list-search').length) {
        Milex.activateSearchAutocomplete('list-search', 'form.form');
    }

    Milex.formBuilderNewComponentInit();
    Milex.iniNewConditionalField();

    var bodyOverflow = {};

    if (mQuery('#milexforms_fields')) {
        //make the fields sortable
        mQuery('#milexforms_fields').sortable({
            items: '.panel',
            cancel: '',
            helper: function(e, ui) {
                ui.children().each(function() {
                    mQuery(this).width(mQuery(this).width());
                });

                // Fix body overflow that messes sortable up
                bodyOverflow.overflowX = mQuery('body').css('overflow-x');
                bodyOverflow.overflowY = mQuery('body').css('overflow-y');
                mQuery('body').css({
                    overflowX: 'visible',
                    overflowY: 'visible'
                });

                return ui;
            },
            scroll: true,
            axis: 'y',
            containment: '#milexforms_fields .drop-here',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
                mQuery(ui.item).attr('style', '');

                mQuery.ajax({
                    type: "POST",
                    url: milexAjaxUrl + "?action=form:reorderFields",
                    data: mQuery('#milexforms_fields').sortable("serialize", {attribute: 'data-sortable-id'}) + "&formId=" + mQuery('#milexform_sessionId').val()
                });
            }
        });

        Milex.initFormFieldButtons();
    }

    if (mQuery('#milexforms_actions')) {
        //make the fields sortable
        mQuery('#milexforms_actions').sortable({
            items: '.panel',
            cancel: '',
            helper: function(e, ui) {
                ui.children().each(function() {
                    mQuery(this).width(mQuery(this).width());
                });

                // Fix body overflow that messes sortable up
                bodyOverflow.overflowX = mQuery('body').css('overflow-x');
                bodyOverflow.overflowY = mQuery('body').css('overflow-y');
                mQuery('body').css({
                    overflowX: 'visible',
                    overflowY: 'visible'
                });

                return ui;
            },
            scroll: true,
            axis: 'y',
            containment: '#milexforms_actions .drop-here',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
                mQuery(ui.item).attr('style', '');

                mQuery.ajax({
                    type: "POST",
                    url: milexAjaxUrl + "?action=form:reorderActions",
                    data: mQuery('#milexforms_actions').sortable("serialize") + "&formId=" + mQuery('#milexform_sessionId').val()
                });
            }
        });

        mQuery('#milexforms_actions .milexform-row').on('dblclick.milexformactions', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });
    }

    if (mQuery('#milexform_formType').length && mQuery('#milexform_formType').val() == '') {
        mQuery('body').addClass('noscroll');
    }

    Milex.initHideItemButton('#milexforms_fields');
    Milex.initHideItemButton('#milexforms_actions');
};

Milex.formBuilderNewComponentInit = function () {
    mQuery('select.form-builder-new-component').change(function (e) {
        mQuery(this).find('option:selected');
        Milex.ajaxifyModal(mQuery(this).find('option:selected'));
        // Reset the dropdown
        mQuery(this).val('');
        mQuery(this).trigger('chosen:updated');
    });
}

Milex.updateFormFields = function () {
    Milex.activateLabelLoadingIndicator('campaignevent_properties_field');

    var formId = mQuery('#campaignevent_properties_form').val();
    Milex.ajaxActionRequest('form:updateFormFields', {'formId': formId}, function(response) {
        if (response.fields) {
            var select = mQuery('#campaignevent_properties_field');
            select.find('option').remove();
            var fieldOptions = {};
            mQuery.each(response.fields, function(key, field) {
                var option = mQuery('<option></option>')
                    .attr('value', field.alias)
                    .text(field.label);
                select.append(option);
                fieldOptions[field.alias] = field.options;
            });
            select.attr('data-field-options', JSON.stringify(fieldOptions));
            select.trigger('chosen:updated');
            Milex.updateFormFieldValues(select);
        }
        Milex.removeLabelLoadingIndicator();
    });
};

Milex.updateFormFieldValues = function (field) {
    field = mQuery(field);
    var fieldValue = field.val();
    var options = jQuery.parseJSON(field.attr('data-field-options'));
    var valueField = mQuery('#campaignevent_properties_value');
    var valueFieldAttrs = {
        'class': valueField.attr('class'),
        'id': valueField.attr('id'),
        'name': valueField.attr('name'),
        'autocomplete': valueField.attr('autocomplete'),
        'value': valueField.attr('value')
    };

    if (typeof options[fieldValue] !== 'undefined' && !mQuery.isEmptyObject(options[fieldValue])) {
        var newValueField = mQuery('<select/>')
            .attr('class', valueFieldAttrs['class'])
            .attr('id', valueFieldAttrs['id'])
            .attr('name', valueFieldAttrs['name'])
            .attr('autocomplete', valueFieldAttrs['autocomplete'])
            .attr('value', valueFieldAttrs['value']);
        mQuery.each(options[fieldValue], function(key, optionVal) {
            var option = mQuery("<option></option>")
                .attr('value', key)
                .text(optionVal);
            newValueField.append(option);
        });
        valueField.replaceWith(newValueField);
    } else {
        var newValueField = mQuery('<input/>')
            .attr('type', 'text')
            .attr('class', valueFieldAttrs['class'])
            .attr('id', valueFieldAttrs['id'])
            .attr('name', valueFieldAttrs['name'])
            .attr('autocomplete', valueFieldAttrs['autocomplete'])
            .attr('value', valueFieldAttrs['value']);
        valueField.replaceWith(newValueField);
    }
};

Milex.formFieldOnLoad = function (container, response) {
    //new field created so append it to the form
    if (response.fieldHtml) {
        var newHtml = response.fieldHtml;
        var fieldId = '#milexform_' + response.fieldId;
        var fieldContainer = mQuery(fieldId).closest('.form-field-wrapper');

        if (mQuery(fieldId).length) {
            //replace content
            mQuery(fieldContainer).replaceWith(newHtml);
            var newField = false;
        } else {
            var parentContainer = mQuery('#milexform_'+response.parent);
            if (parentContainer.length) {
                (parentContainer.parents('.panel:first')).append(newHtml);
            }else {
                //append content
                var panel = mQuery('#milexforms_fields .milexform-button-wrapper').closest('.form-field-wrapper');
                panel.before(newHtml);
            }
            var newField = true;
        }

        // Get the updated element
        var fieldContainer = mQuery(fieldId).closest('.form-field-wrapper');

        //activate new stuff
        mQuery(fieldContainer).find("[data-toggle='ajax']").click(function (event) {
            event.preventDefault();
            return Milex.ajaxifyLink(this, event);
        });

        //initialize tooltips
        mQuery(fieldContainer).find("*[data-toggle='tooltip']").tooltip({html: true});

        //initialize ajax'd modals
        mQuery(fieldContainer).find("[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();
            Milex.ajaxifyModal(this, event);
        });

        Milex.initFormFieldButtons(fieldContainer);
        Milex.initHideItemButton(fieldContainer);

        //show fields panel
        if (!mQuery('#fields-panel').hasClass('in')) {
            mQuery('a[href="#fields-panel"]').trigger('click');
        }

        if (newField) {
            mQuery('.bundle-main-inner-wrapper').scrollTop(mQuery('.bundle-main-inner-wrapper').height());
        }

        if (mQuery('#form-field-placeholder').length) {
            mQuery('#form-field-placeholder').remove();
        }

        Milex.activateChosenSelect(mQuery('.form-builder-new-component'));
        Milex.formBuilderNewComponentInit();
        Milex.iniNewConditionalField();
    }
};

Milex.iniNewConditionalField = function(){
    mQuery('.add-new-conditional-field').click(function (e) {
        e.preventDefault();
        mQuery(this).parent().next().show('normal');
    })
    mQuery('.add-new-conditional-field').parent().next().hide();

}

Milex.initFormFieldButtons = function (container) {
    if (typeof container == 'undefined') {
        mQuery('#milexforms_fields .milexform-row').off(".milexformfields");
        var container = '#milexforms_fields';
    }

    mQuery(container).find('.milexform-row').on('dblclick.milexformfields', function(event) {
        event.preventDefault();
        mQuery(this).closest('.form-field-wrapper').find('.btn-edit').first().click();
    });
};

Milex.formActionOnLoad = function (container, response) {
    //new action created so append it to the form
    if (response.actionHtml) {
        var newHtml = response.actionHtml;
        var actionId = '#milexform_action_' + response.actionId;
        if (mQuery(actionId).length) {
            //replace content
            mQuery(actionId).replaceWith(newHtml);
            var newField = false;
        } else {
            //append content
            mQuery(newHtml).appendTo('#milexforms_actions');
            var newField = true;
        }
        //activate new stuff
        mQuery(actionId + " [data-toggle='ajax']").click(function (event) {
            event.preventDefault();
            return Milex.ajaxifyLink(this, event);
        });
        //initialize tooltips
        mQuery(actionId + " *[data-toggle='tooltip']").tooltip({html: true});

        //initialize ajax'd modals
        mQuery(actionId + " [data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();

            Milex.ajaxifyModal(this, event);
        });

        Milex.initHideItemButton(actionId);

        mQuery('#milexforms_actions .milexform-row').off(".milexform");
        mQuery('#milexforms_actions .milexform-row').on('dblclick.milexformactions', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });

        //show actions panel
        if (!mQuery('#actions-panel').hasClass('in')) {
            mQuery('a[href="#actions-panel"]').trigger('click');
        }

        if (newField) {
            mQuery('.bundle-main-inner-wrapper').scrollTop(mQuery('.bundle-main-inner-wrapper').height());
        }

        if (mQuery('#form-action-placeholder').length) {
            mQuery('#form-action-placeholder').remove();
        }
    }
};

Milex.initHideItemButton = function(container) {
    mQuery(container).find('[data-hide-panel]').click(function(e) {
        e.preventDefault();
        mQuery(this).closest('.panel,.panel2').hide('fast');
    });
}

Milex.onPostSubmitActionChange = function(value) {
    if (value == 'return') {
        //remove required class
        mQuery('#milexform_postActionProperty').prev().removeClass('required');
    } else {
        mQuery('#milexform_postActionProperty').prev().addClass('required');
    }

    mQuery('#milexform_postActionProperty').next().html('');
    mQuery('#milexform_postActionProperty').parent().removeClass('has-error');
};

Milex.selectFormType = function(formType) {
    if (formType == 'standalone') {
        mQuery('option.action-standalone-only').removeClass('hide');
        mQuery('.page-header h3').text(milexLang.newStandaloneForm);
    } else {
        mQuery('option.action-standalone-only').addClass('hide');
        mQuery('.page-header h3').text(milexLang.newCampaignForm);
    }

    mQuery('.available-actions select').trigger('chosen:updated');

    mQuery('#milexform_formType').val(formType);

    mQuery('body').removeClass('noscroll');

    mQuery('.form-type-modal').remove();
    mQuery('.form-type-modal-backdrop').remove();
};