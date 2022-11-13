<?php

$firewalls = [
    'install' => [
        'pattern'   => '^/installer',
        'anonymous' => true,
        'context'   => 'milex',
        'security'  => false,
    ],
    'dev' => [
        'pattern'   => '^/(_(profiler|wdt)|css|images|js)/',
        'security'  => true,
        'anonymous' => true,
    ],
    'login' => [
        'pattern'   => '^/s/login$',
        'anonymous' => true,
        'context'   => 'milex',
    ],
    'sso_login' => [
        'pattern'            => '^/s/sso_login',
        'anonymous'          => true,
        'milex_plugin_auth' => true,
        'context'            => 'milex',
    ],
    'saml_login' => [
        'pattern'   => '^/s/saml/login$',
        'anonymous' => true,
        'context'   => 'milex',
    ],
    'saml_discovery' => [
        'pattern'   => '^/saml/discovery$',
        'anonymous' => true,
        'context'   => 'milex',
    ],
    'oauth2_token' => [
        'pattern'  => '^/oauth/v2/token',
        'security' => false,
    ],
    'oauth2_area' => [
        'pattern'    => '^/oauth/v2/authorize',
        'form_login' => [
            'provider'   => 'user_provider',
            'check_path' => '/oauth/v2/authorize_login_check',
            'login_path' => '/oauth/v2/authorize_login',
        ],
        'anonymous' => true,
    ],
    'api' => [
        'pattern'            => '^/api',
        'fos_oauth'          => true,
        'milex_plugin_auth' => true,
        'stateless'          => true,
        'http_basic'         => true,
    ],
    'main' => [
        'pattern'       => '^/s/',
        'light_saml_sp' => [
            'provider'        => 'user_provider',
            'success_handler' => 'milex.security.authentication_handler',
            'failure_handler' => 'milex.security.authentication_handler',
            'user_creator'    => 'milex.security.saml.user_creator',
            'username_mapper' => 'milex.security.saml.username_mapper',

            // Environment variables will overwrite these with the standard login URLs if SAML is disabled
            'login_path'      => '%env(MILEX_SAML_LOGIN_PATH)%', // '/s/saml/login',,
            'check_path'      => '%env(MILEX_SAML_LOGIN_CHECK_PATH)%', // '/s/saml/login_check',
        ],
        'simple_form' => [
            'authenticator'        => 'milex.user.form_authenticator',
            'csrf_token_generator' => 'security.csrf.token_manager',
            'success_handler'      => 'milex.security.authentication_handler',
            'failure_handler'      => 'milex.security.authentication_handler',
            'login_path'           => '/s/login',
            'check_path'           => '/s/login_check',
        ],
        'logout' => [
            'handlers' => [
                'milex.security.logout_handler',
            ],
            'path'   => '/s/logout',
            'target' => '/s/login',
        ],
        'remember_me' => [
            'secret'   => '%milex.rememberme_key%',
            'lifetime' => '%milex.rememberme_lifetime%',
            'path'     => '%milex.rememberme_path%',
            'domain'   => '%milex.rememberme_domain%',
        ],
        'fos_oauth'     => true,
        'context'       => 'milex',
    ],
    'public' => [
        'pattern'   => '^/',
        'anonymous' => true,
        'context'   => 'milex',
    ],
];

if (!$container->getParameter('milex.famework.csrf_protection')) {
    unset($firewalls['main']['simple_form']['csrf_token_generator']);
}

$container->loadFromExtension(
    'security',
    [
        'providers' => [
            'user_provider' => [
                'id' => 'milex.user.provider',
            ],
        ],
        'encoders' => [
            'Symfony\Component\Security\Core\User\User' => [
                'algorithm'  => 'bcrypt',
                'iterations' => 12,
            ],
            'Milex\UserBundle\Entity\User' => [
                'algorithm'  => 'bcrypt',
                'iterations' => 12,
            ],
        ],
        'role_hierarchy' => [
            'ROLE_ADMIN' => 'ROLE_USER',
        ],
        'firewalls'      => $firewalls,
        'access_control' => [
            ['path' => '^/api', 'roles' => 'IS_AUTHENTICATED_FULLY'],
            ['path' => '^/efconnect', 'roles' => 'IS_AUTHENTICATED_FULLY'],
            ['path' => '^/elfinder', 'roles' => 'IS_AUTHENTICATED_FULLY'],
        ],
    ]
);

$container->setParameter('milex.saml_idp_entity_id', '%env(MILEX_SAML_ENTITY_ID)%');
$container->loadFromExtension(
    'light_saml_symfony_bridge',
    [
        'own' => [
            'entity_id' => '%milex.saml_idp_entity_id%',
        ],
        'store' => [
            'id_state' => 'milex.security.saml.id_store',
        ],
    ]
);

$this->import('security_api.php');

// List config keys we do not want the user to change via the config UI
$restrictedConfigFields = [
    'db_driver',
    'db_host',
    'db_table_prefix',
    'db_name',
    'db_user',
    'db_password',
    'db_path',
    'db_port',
    'secret_key',
];

// List config keys that are dev mode only
if ('prod' == $container->getParameter('kernel.environment')) {
    $restrictedConfigFields = array_merge($restrictedConfigFields, ['transifex_username', 'transifex_password']);
}

$container->setParameter('milex.security.restrictedConfigFields', $restrictedConfigFields);
$container->setParameter('milex.security.restrictedConfigFields.displayMode', \Milex\ConfigBundle\Form\Helper\RestrictionHelper::MODE_REMOVE);

/*
 * Optional security parameters
 * milex.security.disableUpdates = disables remote checks for updates
 * milex.security.restrictedConfigFields.displayMode = accepts either remove or mask; mask will disable the input with a "Set by system" message
 */
$container->setParameter('milex.security.disableUpdates', false);
