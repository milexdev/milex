Milex.getNetworkFormAction = function(networkType) {
    // removes errors when network type properties has changed
    if (networkType && mQuery(networkType).val() && mQuery(networkType).closest('.form-group').hasClass('has-error')) {
        mQuery(networkType).closest('.form-group').removeClass('has-error');
        if (mQuery(networkType).next().hasClass('help-block')) {
            mQuery(networkType).next().remove();
        }
    }

    Milex.activateLabelLoadingIndicator('monitoring_networkType');

    var query = "action=plugin:milexSocial:getNetworkForm&networkType=" + mQuery(networkType).val();

    mQuery.ajax({
        url: milexAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                // pushes response into container element
                mQuery('#properties-container').html(response.html);

                // sends markup through core js parsers
                if (response.html != '') {
                    Milex.onPageLoad('#properties-container', response);
                }
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

/*
 * watches the compose field and updates various parts of the modal and text area
 */
Milex.composeSocialWatcher = function() {
    // the text area
    var input = mQuery('textarea.tweet-message');

    // on load
    Milex.updateCharacterCount();

    // watch the text area keyup
    input.on('keyup', function(){
        Milex.updateCharacterCount();
    });

    var pageId  = mQuery('select.tweet-insert-page');
    var assetId = mQuery('select.tweet-insert-asset');
    var handle  = mQuery('button.tweet-insert-handle');

    pageId.on('change', function() {
        Milex.insertSocialLink(pageId.val(), 'pagelink', false);
    });

    assetId.on('change', function() {
        Milex.insertSocialLink(assetId.val(), 'assetlink', false);
    });

    handle.on('click', function() {
       Milex.insertSocialLink(false, 'twitter_handle', true);
    });
};

/*
 * gets the count of the text area and returns (140 - count)
 */
Milex.getCharacterCount = function() {
    var tweetLenght = 280;

    var currentLength = mQuery('textarea#twitter_tweet_text');

    return (tweetLenght - currentLength.val().length);
};

/*
 * sets the content of the character count span
 */
Milex.updateCharacterCount = function() {
    var tweetCount = Milex.getCharacterCount();

    var countContainer = mQuery('#character-count span');

    countContainer.text(tweetCount);
};

/*
 * inserts a link placeholder into the text box.
 *
 * @id     the id of the link placeholder
 * @type   the type of link to insert
 * @skipId if the id is blank and this is true it'll still insert the link
 */
Milex.insertSocialLink = function(id, type, skipId) {

    // if there is no id and skipID is false then exit
    if (! id && ! skipId) {
        return;
    }

    // if we need to skip the id state just leave it out
    if (skipId) {
        var link = '{' + type + '}';
    }
    else {
        var link = '{' + type + '=' + id + '}';
    }

    var textarea = mQuery('textarea.tweet-message');
    var currentVal = textarea.val();
    var newVal = (currentVal) ? currentVal + ' ' + link : link;
    textarea.val(newVal);

    Milex.updateCharacterCount();
};