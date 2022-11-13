<?php

namespace Milex\ConfigBundle;

/**
 * Class ConfigEvents
 * Events available for ConfigBundle.
 */
final class ConfigEvents
{
    /**
     * The milex.config_on_generate event is thrown when the configuration form is generated.
     *
     * The event listener receives a
     * Milex\ConfigBundle\Event\ConfigGenerateEvent instance.
     *
     * @var string
     */
    const CONFIG_ON_GENERATE = 'milex.config_on_generate';

    /**
     * The milex.config_pre_save event is thrown right before config data are saved.
     *
     * The event listener receives a Milex\ConfigBundle\Event\ConfigEvent instance.
     *
     * @var string
     */
    const CONFIG_PRE_SAVE = 'milex.config_pre_save';

    /**
     * The milex.config_post_save event is thrown right after config data are saved.
     *
     * The event listener receives a Milex\ConfigBundle\Event\ConfigEvent instance.
     *
     * @var string
     */
    const CONFIG_POST_SAVE = 'milex.config_post_save';
}
