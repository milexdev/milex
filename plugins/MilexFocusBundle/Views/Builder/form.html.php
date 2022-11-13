<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
use Milex\CoreBundle\Helper\InputHelper;

$formName = '_'.strtolower(
        InputHelper::alphanum(
            InputHelper::transliterate(
                $form->getName()
            )
        ).'_focus'
    );
$jsFormName = ltrim($formName, '_');
$fields     = $form->getFields();
$required   = [];
?>

<!-- START FOCUS FORM -->

<?php
echo $view->render('MilexFormBundle:Builder:script.html.php', ['form' => $form, 'formName' => $formName]); ?>
<script>
    var MilexFocusHandler = function (messageType, message) {
        // Store the HTML
        var wrapper = document.getElementById('milexform_wrapper<?php echo $formName; ?>');
        var innerForm = wrapper.getElementsByClassName('milexform-innerform');
        innerForm[0].style.display = "none";

        <?php if ('page' == $style): ?>
        document.getElementById('milexform<?php echo $formName; ?>_' + messageType).style.fontSize = "2em";
        <?php elseif ('bar' != $style): ?>
        document.getElementById('milexform<?php echo $formName; ?>_' + messageType).style.fontSize = "1.1em";
        <?php endif; ?>

        var headline = document.getElementsByClassName('mf-headline');
        if (headline.length) {
            headline[0].style.display = "none";
        }

        var tagline = document.getElementsByClassName('mf-tagline');
        if (tagline.length) {
            tagline[0].style.display = "none";
        }

        if (message) {
            document.getElementById('milexform<?php echo $formName; ?>_' + messageType).innerHTML = message;
        }

        setTimeout(function () {
            if (headline.length) {
                <?php if ('bar' == $style): ?>
                headline[0].style.display = "inline-block";
                <?php else : ?>
                headline[0].style.display = "block";
                <?php endif; ?>
            }
            if (tagline.length) {
                tagline[0].style.display = "inherit";
            }

            innerForm[0].style.display = "inherit";
            document.getElementById('milexform<?php echo $formName; ?>_' + messageType).innerHTML = '';
        }, (messageType == 'error') ? 1500 : 5000);
    }
    if (typeof MilexFormCallback == 'undefined') {
        var MilexFormCallback = {};
    }
    MilexFormCallback["<?php echo $jsFormName; ?>"] = {
        onMessageSet: function (data) {
            if (data.message) {
                MilexFocusHandler(data.type);
            }
        },
        onErrorMark: function (data) {
            if (data.validationMessage) {
                MilexFocusHandler('error', data.validationMessage);

                return true;
            }
        },
        onResponse: function (data) {
            if (data.download) {
                // Hit the download in the iframe
                document.getElementById('milexiframe<?php echo $formName; ?>').src = data.download;

                // Register a callback for a redirect
                if (data.redirect) {
                    setTimeout(function () {
                        window.top.location = data.redirect;
                    }, 2000);
                }

                return true;
            } else if (data.redirect) {
                window.top.location = data.redirect;

                return true;
            }

            return false;
        }
    }
</script>

<?php
$formExtra = <<<EXTRA
<input type="hidden" name="milexform[focusId]" id="milexform{$formName}_focus_id" value="$focusId"/>
EXTRA;

echo $view->render('MilexFormBundle:Builder:form.html.php', [
        'form'           => $form,
        'formPages'      => $pages,
        'lastFormPage'   => $lastPage,
        'formExtra'      => $formExtra,
        'action'         => ($preview) ? '#' : null,
        'suffix'         => '_focus',
        'contactFields'  => $contactFields,
        'companyFields'  => $companyFields,
        'viewOnlyFields' => $viewOnlyFields,
    ]
);
?>

<!-- END FOCUS FORM -->