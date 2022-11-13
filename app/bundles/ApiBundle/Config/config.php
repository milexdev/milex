<?php

return [
    'routes' => [
        'public' => [
            // OAuth2
            'fos_oauth_server_token' => [
                'path'       => '/oauth/v2/token',
                'controller' => 'fos_oauth_server.controller.token:tokenAction',
                'method'     => 'GET|POST',
            ],
            'fos_oauth_server_authorize' => [
                'path'       => '/oauth/v2/authorize',
                'controller' => 'MilexApiBundle:oAuth2/Authorize:authorize',
                'method'     => 'GET|POST',
            ],
            'milex_oauth2_server_auth_login' => [
                'path'       => '/oauth/v2/authorize_login',
                'controller' => 'MilexApiBundle:oAuth2/Security:login',
                'method'     => 'GET|POST',
            ],
            'milex_oauth2_server_auth_login_check' => [
                'path'       => '/oauth/v2/authorize_login_check',
                'controller' => 'MilexApiBundle:oAuth2/Security:loginCheck',
                'method'     => 'GET|POST',
            ],
        ],
        'main' => [
            // Clients
            'milex_client_index' => [
                'path'       => '/credentials/{page}',
                'controller' => 'MilexApiBundle:Client:index',
            ],
            'milex_client_action' => [
                'path'       => '/credentials/{objectAction}/{objectId}',
                'controller' => 'MilexApiBundle:Client:execute',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'items' => [
                'milex.api.client.menu.index' => [
                    'route'     => 'milex_client_index',
                    'iconClass' => 'fa-puzzle-piece',
                    'access'    => 'api:clients:view',
                    'checks'    => [
                        'parameters' => [
                            'api_enabled' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'services' => [
        'controllers' => [
            'milex.api.oauth2.authorize_controller' => [
                'class'     => \Milex\ApiBundle\Controller\oAuth2\AuthorizeController::class,
                'arguments' => [
                    'request_stack',
                    'fos_oauth_server.authorize.form',
                    'fos_oauth_server.authorize.form.handler.default',
                    'fos_oauth_server.server',
                    'templating',
                    'security.token_storage',
                    'router',
                    'fos_oauth_server.client_manager.default',
                    'event_dispatcher',
                    'session',
                ],
            ],
        ],
        'events' => [
            'milex.api.subscriber' => [
                'class'     => \Milex\ApiBundle\EventListener\ApiSubscriber::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'translator',
                ],
            ],
            'milex.api.client.subscriber' => [
                'class'     => \Milex\ApiBundle\EventListener\ClientSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.api.configbundle.subscriber' => [
                'class' => \Milex\ApiBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.api.search.subscriber' => [
                'class'     => \Milex\ApiBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.api.model.client',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.api.rate_limit_generate_key.subscriber' => [
              'class'     => \Milex\ApiBundle\EventListener\RateLimitGenerateKeySubscriber::class,
              'arguments' => [
                'milex.helper.core_parameters',
              ],
            ],
        ],
        'forms' => [
            'milex.form.type.apiclients' => [
                'class'     => \Milex\ApiBundle\Form\Type\ClientType::class,
                'arguments' => [
                    'request_stack',
                    'translator',
                    'validator',
                    'session',
                    'router',
                ],
            ],
            'milex.form.type.apiconfig' => [
                'class' => 'Milex\ApiBundle\Form\Type\ConfigType',
            ],
        ],
        'helpers' => [
            'milex.api.helper.entity_result' => [
                'class' => \Milex\ApiBundle\Helper\EntityResultHelper::class,
            ],
        ],
        'other' => [
            'milex.api.oauth.event_listener' => [
                'class'     => 'Milex\ApiBundle\EventListener\OAuthEventListener',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'milex.security',
                    'translator',
                ],
                'tags' => [
                    'kernel.event_listener',
                    'kernel.event_listener',
                ],
                'tagArguments' => [
                    [
                        'event'  => 'fos_oauth_server.pre_authorization_process',
                        'method' => 'onPreAuthorizationProcess',
                    ],
                    [
                        'event'  => 'fos_oauth_server.post_authorization_process',
                        'method' => 'onPostAuthorizationProcess',
                    ],
                ],
            ],
            'fos_oauth_server.security.authentication.listener.class' => 'Milex\ApiBundle\Security\OAuth2\Firewall\OAuthListener',
            'jms_serializer.metadata.annotation_driver'               => 'Milex\ApiBundle\Serializer\Driver\AnnotationDriver',
            'jms_serializer.metadata.api_metadata_driver'             => [
                'class' => 'Milex\ApiBundle\Serializer\Driver\ApiMetadataDriver',
            ],
            'milex.validator.oauthcallback' => [
                'class' => 'Milex\ApiBundle\Form\Validator\Constraints\OAuthCallbackValidator',
                'tag'   => 'validator.constraint_validator',
            ],
        ],
        'models' => [
            'milex.api.model.client' => [
                'class'     => 'Milex\ApiBundle\Model\ClientModel',
                'arguments' => [
                    'request_stack',
                ],
            ],
        ],
    ],

    'parameters' => [
        'api_enabled'                       => false,
        'api_enable_basic_auth'             => false,
        'api_oauth2_access_token_lifetime'  => 60,
        'api_oauth2_refresh_token_lifetime' => 14,
        'api_batch_max_limit'               => 200,
        'api_rate_limiter_limit'            => 0,
        'api_rate_limiter_cache'            => [
            'adapter' => 'cache.adapter.filesystem',
        ],
    ],
];
