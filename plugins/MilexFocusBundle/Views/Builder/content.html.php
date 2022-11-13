<?php

/*
 * @copyright   2016 Milex, Inc. All rights reserved
 * @author      Milex, Inc
 *
 * @link        https://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$templateBase = 'MilexFocusBundle:Builder\\'.ucfirst($focus['style']).':index.html.php';
if (!isset($preview)) {
    $preview = false;
}

if (!isset($clickUrl)) {
    $clickUrl = '#';
}

$props = $focus['properties'];
?>

<div>
    <style scoped>
        .milex-focus {
            font-family: <?php echo $props['content']['font']; ?>;
            color: #<?php echo $props['colors']['text']; ?>;
        }

        <?php if (isset($props['colors'])): ?>

        .mf-content a.mf-link, .mf-content .milexform-button, .mf-content .milexform-pagebreak {
            background-color: #<?php echo $props['colors']['button']; ?>;
            color: #<?php echo $props['colors']['button_text']; ?>;
        }

        .milexform-input:focus, select:focus {
            border: 1px solid #<?php echo $props['colors']['button']; ?>;
        }

        <?php endif; ?>
        <?php
        if (!empty($preview)):
            echo $view->render('MilexFocusBundle:Builder:style.less.php',
                [
                    'preview' => true,
                    'focus'   => $focus,
                ]
            );
        endif;
        ?>
    </style>
    <?php echo $view->render(
        $templateBase,
        [
            'focus'    => $focus,
            'preview'  => $preview,
            'clickUrl' => $clickUrl,
            'htmlMode' => $htmlMode,
        ]
    );

    ?>
    <style scoped>
    <?php

    if (isset($focus['properties']['content']['css'])) {
        echo $focus['properties']['content']['css'];
    }

    ?>
    </style>
    <?php
    // Add view tracking image
    if (!$preview): ?>

        <img src="<?php echo $view['router']->url(
            'milex_focus_pixel',
            ['id' => $focus['id']],
            true
        ); ?>" alt="Milex Focus" style="display: none;"/>
    <?php endif; ?>
</div>