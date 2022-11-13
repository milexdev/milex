<?php

namespace Milex\UserBundle;

/**
 * Class UserEvents.
 *
 * Events available for UserBundle
 */
final class UserEvents
{
    /**
     * The milex.user_pre_save event is dispatched right before a user is persisted.
     *
     * The event listener receives a Milex\UserBundle\Event\UserEvent instance.
     *
     * @var string
     */
    const USER_PRE_SAVE = 'milex.user_pre_save';

    /**
     * The milex.user_post_save event is dispatched right after a user is persisted.
     *
     * The event listener receives a Milex\UserBundle\Event\UserEvent instance.
     *
     * @var string
     */
    const USER_POST_SAVE = 'milex.user_post_save';

    /**
     * The milex.user_pre_delete event is dispatched prior to when a user is deleted.
     *
     * The event listener receives a Milex\UserBundle\Event\UserEvent instance.
     *
     * @var string
     */
    const USER_PRE_DELETE = 'milex.user_pre_delete';

    /**
     * The milex.user_post_delete event is dispatched after a user is deleted.
     *
     * The event listener receives a Milex\UserBundle\Event\UserEvent instance.
     *
     * @var string
     */
    const USER_POST_DELETE = 'milex.user_post_delete';

    /**
     * The milex.role_pre_save event is dispatched right before a role is persisted.
     *
     * The event listener receives a Milex\UserBundle\Event\RoleEvent instance.
     *
     * @var string
     */
    const ROLE_PRE_SAVE = 'milex.role_pre_save';

    /**
     * The milex.role_post_save event is dispatched right after a role is persisted.
     *
     * The event listener receives a Milex\UserBundle\Event\RoleEvent instance.
     *
     * @var string
     */
    const ROLE_POST_SAVE = 'milex.role_post_save';

    /**
     * The milex.role_pre_delete event is dispatched prior a role being deleted.
     *
     * The event listener receives a Milex\UserBundle\Event\RoleEvent instance.
     *
     * @var string
     */
    const ROLE_PRE_DELETE = 'milex.role_pre_delete';

    /**
     * The milex.role_post_delete event is dispatched after a role is deleted.
     *
     * The event listener receives a Milex\UserBundle\Event\RoleEvent instance.
     *
     * @var string
     */
    const ROLE_POST_DELETE = 'milex.role_post_delete';

    /**
     * The milex.user_logout event is dispatched during the logout routine giving a chance to carry out tasks before
     * the session is lost.
     *
     * The event listener receives a Milex\UserBundle\Event\LogoutEvent instance.
     *
     * @var string
     */
    const USER_LOGOUT = 'milex.user_logout';

    /**
     * The milex.user_login event is dispatched right after a user logs in.
     *
     * The event listener receives a Milex\UserBundle\Event\LoginEvent instance.
     *
     * @var string
     */
    const USER_LOGIN = 'milex.user_login';

    /**
     * The milex.user_form_authentication event is dispatched when a user logs in so that listeners can authenticate a user, i.e. via a 3rd party service.
     *
     * The event listener receives a Milex\UserBundle\Event\AuthenticationEvent instance.
     *
     * @var string
     */
    const USER_FORM_AUTHENTICATION = 'milex.user_form_authentication';

    /**
     * The milex.user_pre_authentication event is dispatched when a user browses a page under /s/ except for /login. This allows support for
     * 3rd party authentication providers outside the login form.
     *
     * The event listener receives a Milex\UserBundle\Event\AuthenticationEvent instance.
     *
     * @var string
     */
    const USER_PRE_AUTHENTICATION = 'milex.user_pre_authentication';

    /**
     * The milex.user_authentication_content event is dispatched to collect HTML from plugins to be injected into the UI to assist with
     * authentication.
     *
     * The event listener receives a Milex\UserBundle\Event\AuthenticationContentEvent instance.
     *
     * @var string
     */
    const USER_AUTHENTICATION_CONTENT = 'milex.user_authentication_content';
}
