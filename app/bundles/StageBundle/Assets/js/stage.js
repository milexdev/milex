Milex.getStageActionPropertiesForm = function(actionType) {
    Milex.activateLabelLoadingIndicator('stage_type');

    var query = "action=stage:getActionForm&actionType=" + actionType;
    mQuery.ajax({
        url: milexAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                mQuery('#stageActionProperties').html(response.html);
                Milex.onPageLoad('#stageActionProperties', response);
            }
        },
        error: function (request, textStatus, errorThrown) {
            Milex.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function() {
            Milex.removeLabelLoadingIndicator();
        }
    });
};