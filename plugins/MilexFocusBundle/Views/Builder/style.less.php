<?php

/*
 * @copyright   2016 Milex, Inc. All rights reserved
 * @author      Milex, Inc
 *
 * @link        https://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!isset($preview)) {
    $preview = false;
}

ob_start();
?>
.mf-bar-iframe {
    z-index: 19000;
}

.mf-content {
    line-height: 1.1;

    .mf-inner-container {
        margin-top: 20px;
    }

    a.mf-link, .milexform-button, .milexform-pagebreak {
        padding: 5px 15px;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        border: none;
    }

    a.mf-link:hover, .milexform-button:hover, .milexform-pagebreak:hover {
        opacity: 0.9;
        text-decoration: none;
        border: none;
    }

    .milexform-pagebreak {
        width: auto !important;
    }
}

.milex-focus {
    <?php if ($preview): ?>

    .milexform-row {
        min-height: 0px;
    }
    <?php endif; ?>

    .milexform_wrapper form {
        padding: 0;
        margin: 0;
    }

    .milexform-input, select {
        border-radius: 2px;
        padding: 5px 8px;
        color: #757575;
        border: 1px solid #ababab;
    }

    .milexform-input:focus, select:focus {
        outline: none;
        border: 1px solid #757575;
    }
}

<?php

echo $view->render(
    'MilexFocusBundle:Builder\Bar:style.less.php',
    [
        'preview' => $preview,
    ]
);

echo $view->render(
    'MilexFocusBundle:Builder\Modal:style.less.php',
    [
        'preview' => $preview,
    ]
);

echo $view->render(
    'MilexFocusBundle:Builder\Notification:style.less.php',
    [
        'preview' => $preview,
    ]
);

echo $view->render(
    'MilexFocusBundle:Builder\Page:style.less.php',
    [
        'preview' => $preview,
    ]
);

$less = ob_get_clean();

require_once __DIR__.'/../../Include/lessc.inc.php';
$compiler = new \lessc();
$css      = $compiler->compile($less);

if (empty($preview) && 'dev' != $app->getEnvironment()) {
    $css = \Minify_CSS::minify($css);
}

echo $css;
