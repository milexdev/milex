<?php

namespace Milex\ApiBundle;

/**
 * Class ApiEvents.
 */
final class ApiEvents
{
    /**
     * The milex.client_pre_save event is thrown right before an API client is persisted.
     *
     * The event listener receives a Milex\ApiBundle\Event\ClientEvent instance.
     *
     * @var string
     */
    const CLIENT_PRE_SAVE = 'milex.client_pre_save';

    /**
     * The milex.client_post_save event is thrown right after an API client is persisted.
     *
     * The event listener receives a Milex\ApiBundle\Event\ClientEvent instance.
     *
     * @var string
     */
    const CLIENT_POST_SAVE = 'milex.client_post_save';

    /**
     * The milex.client_post_delete event is thrown after an API client is deleted.
     *
     * The event listener receives a Milex\ApiBundle\Event\ClientEvent instance.
     *
     * @var string
     */
    const CLIENT_POST_DELETE = 'milex.client_post_delete';

    /**
     * The milex.build_api_route event is thrown to build Milex API routes.
     *
     * The event listener receives a Milex\CoreBundle\Event\RouteEvent instance.
     *
     * @var string
     */
    const BUILD_ROUTE = 'milex.build_api_route';

    /**
     * The milex.api_on_entity_pre_save event is thrown after an entity about to be saved via API.
     *
     * The event listener receives a Milex\ApiBundle\Event\ApiEntityEvent instance.
     *
     * @var string
     */
    const API_ON_ENTITY_PRE_SAVE = 'milex.api_on_entity_pre_save';

    /**
     * The milex.api_on_entity_post_save event is thrown after an entity is saved via API.
     *
     * The event listener receives a Milex\ApiBundle\Event\ApiEntityEvent instance.
     *
     * @var string
     */
    const API_ON_ENTITY_POST_SAVE = 'milex.api_on_entity_post_save';
}
