<?php

return [
    'menu' => [
        'admin' => [
            'milex.user.users' => [
                'access'    => 'user:users:view',
                'route'     => 'milex_user_index',
                'iconClass' => 'fa-users',
            ],
            'milex.user.roles' => [
                'access'    => 'user:roles:view',
                'route'     => 'milex_role_index',
                'iconClass' => 'fa-lock',
            ],
        ],
    ],

    'routes' => [
        'main' => [
            'login' => [
                'path'       => '/login',
                'controller' => 'MilexUserBundle:Security:login',
            ],
            'milex_user_logincheck' => [
                'path'       => '/login_check',
                'controller' => 'MilexUserBundle:Security:loginCheck',
            ],
            'milex_user_logout' => [
                'path' => '/logout',
            ],
            'milex_sso_login' => [
                'path'       => '/sso_login/{integration}',
                'controller' => 'MilexUserBundle:Security:ssoLogin',
            ],
            'milex_sso_login_check' => [
                'path'       => '/sso_login_check/{integration}',
                'controller' => 'MilexUserBundle:Security:ssoLoginCheck',
            ],
            'lightsaml_sp.login' => [
                'path'       => '/saml/login',
                'controller' => 'LightSamlSpBundle:Default:login',
            ],
            'lightsaml_sp.login_check' => [
                'path' => '/saml/login_check',
            ],
            'milex_user_index' => [
                'path'       => '/users/{page}',
                'controller' => 'MilexUserBundle:User:index',
            ],
            'milex_user_action' => [
                'path'       => '/users/{objectAction}/{objectId}',
                'controller' => 'MilexUserBundle:User:execute',
            ],
            'milex_role_index' => [
                'path'       => '/roles/{page}',
                'controller' => 'MilexUserBundle:Role:index',
            ],
            'milex_role_action' => [
                'path'       => '/roles/{objectAction}/{objectId}',
                'controller' => 'MilexUserBundle:Role:execute',
            ],
            'milex_user_account' => [
                'path'       => '/account',
                'controller' => 'MilexUserBundle:Profile:index',
            ],
        ],

        'api' => [
            'milex_api_usersstandard' => [
                'standard_entity' => true,
                'name'            => 'users',
                'path'            => '/users',
                'controller'      => 'MilexUserBundle:Api\UserApi',
            ],
            'milex_api_getself' => [
                'path'       => '/users/self',
                'controller' => 'MilexUserBundle:Api\UserApi:getSelf',
            ],
            'milex_api_checkpermission' => [
                'path'       => '/users/{id}/permissioncheck',
                'controller' => 'MilexUserBundle:Api\UserApi:isGranted',
                'method'     => 'POST',
            ],
            'milex_api_getuserroles' => [
                'path'       => '/users/list/roles',
                'controller' => 'MilexUserBundle:Api\UserApi:getRoles',
            ],
            'milex_api_rolesstandard' => [
                'standard_entity' => true,
                'name'            => 'roles',
                'path'            => '/roles',
                'controller'      => 'MilexUserBundle:Api\RoleApi',
            ],
        ],
        'public' => [
            'milex_user_passwordreset' => [
                'path'       => '/passwordreset',
                'controller' => 'MilexUserBundle:Public:passwordReset',
            ],
            'milex_user_passwordresetconfirm' => [
                'path'       => '/passwordresetconfirm',
                'controller' => 'MilexUserBundle:Public:passwordResetConfirm',
            ],
            'lightsaml_sp.metadata' => [
                'path'       => '/saml/metadata.xml',
                'controller' => 'LightSamlSpBundle:Default:metadata',
            ],
            'lightsaml_sp.discovery' => [
                'path'       => '/saml/discovery',
                'controller' => 'LightSamlSpBundle:Default:discovery',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'milex.user.subscriber' => [
                'class'     => \Milex\UserBundle\EventListener\UserSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.user.search.subscriber' => [
                'class'     => \Milex\UserBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.user.model.user',
                    'milex.user.model.role',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.user.config.subscriber' => [
                'class' => \Milex\UserBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.user.route.subscriber' => [
                'class'     => \Milex\UserBundle\EventListener\SAMLSubscriber::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'router',
                ],
            ],
            'milex.user.security_subscriber' => [
                'class'     => \Milex\UserBundle\EventListener\SecuritySubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.user' => [
                'class'     => \Milex\UserBundle\Form\Type\UserType::class,
                'arguments' => [
                    'translator',
                    'milex.user.model.user',
                    'milex.helper.language',
                ],
            ],
            'milex.form.type.role' => [
                'class' => \Milex\UserBundle\Form\Type\RoleType::class,
            ],
            'milex.form.type.permissions' => [
                'class' => \Milex\UserBundle\Form\Type\PermissionsType::class,
            ],
            'milex.form.type.permissionlist' => [
                'class' => \Milex\UserBundle\Form\Type\PermissionListType::class,
            ],
            'milex.form.type.passwordreset' => [
                'class' => \Milex\UserBundle\Form\Type\PasswordResetType::class,
            ],
            'milex.form.type.passwordresetconfirm' => [
                'class' => \Milex\UserBundle\Form\Type\PasswordResetConfirmType::class,
            ],
            'milex.form.type.user_list' => [
                'class'     => \Milex\UserBundle\Form\Type\UserListType::class,
                'arguments' => 'milex.user.model.user',
            ],
            'milex.form.type.role_list' => [
                'class'     => \Milex\UserBundle\Form\Type\RoleListType::class,
                'arguments' => 'milex.user.model.role',
            ],
            'milex.form.type.userconfig' => [
                'class'     => \Milex\UserBundle\Form\Type\ConfigType::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'translator',
                ],
            ],
        ],
        'other' => [
            // Authentication
            'milex.user.manager' => [
                'class'     => 'Doctrine\ORM\EntityManager',
                'arguments' => 'Milex\UserBundle\Entity\User',
                'factory'   => ['@doctrine', 'getManagerForClass'],
            ],
            'milex.user.repository' => [
                'class'     => 'Milex\UserBundle\Entity\UserRepository',
                'arguments' => 'Milex\UserBundle\Entity\User',
                'factory'   => ['@milex.user.manager', 'getRepository'],
            ],
            'milex.user.token.repository' => [
                'class'     => 'Milex\UserBundle\Entity\UserTokenRepository',
                'arguments' => 'Milex\UserBundle\Entity\UserToken',
                'factory'   => ['@doctrine', 'getRepository'],
            ],
            'milex.permission.manager' => [
                'class'     => 'Doctrine\ORM\EntityManager',
                'arguments' => 'Milex\UserBundle\Entity\Permission',
                'factory'   => ['@doctrine', 'getManagerForClass'],
            ],
            'milex.permission.repository' => [
                'class'     => 'Milex\UserBundle\Entity\PermissionRepository',
                'arguments' => 'Milex\UserBundle\Entity\Permission',
                'factory'   => ['@milex.permission.manager', 'getRepository'],
            ],
            'milex.user.form_authenticator' => [
                'class'     => 'Milex\UserBundle\Security\Authenticator\FormAuthenticator',
                'arguments' => [
                    'milex.helper.integration',
                    'security.password_encoder',
                    'event_dispatcher',
                    'request_stack',
                ],
            ],
            'milex.user.preauth_authenticator' => [
                'class'     => 'Milex\UserBundle\Security\Authenticator\PreAuthAuthenticator',
                'arguments' => [
                    'milex.helper.integration',
                    'event_dispatcher',
                    'request_stack',
                    '', // providerKey
                    '', // User provider
                ],
                'public' => false,
            ],
            'milex.user.provider' => [
                'class'     => 'Milex\UserBundle\Security\Provider\UserProvider',
                'arguments' => [
                    'milex.user.repository',
                    'milex.permission.repository',
                    'session',
                    'event_dispatcher',
                    'security.password_encoder',
                ],
            ],
            'milex.security.authentication_listener' => [
                'class'     => 'Milex\UserBundle\Security\Firewall\AuthenticationListener',
                'arguments' => [
                    'milex.security.authentication_handler',
                    'security.token_storage',
                    'security.authentication.manager',
                    'monolog.logger',
                    'event_dispatcher',
                    '', // providerKey
                    'milex.permission.repository',
                    'doctrine.orm.default_entity_manager',
                ],
                'public' => false,
            ],
            'milex.security.authentication_handler' => [
                'class'     => \Milex\UserBundle\Security\Authentication\AuthenticationHandler::class,
                'arguments' => [
                    'router',
                ],
            ],
            'milex.security.logout_handler' => [
                'class'     => 'Milex\UserBundle\Security\Authentication\LogoutHandler',
                'arguments' => [
                    'milex.user.model.user',
                    'event_dispatcher',
                    'milex.helper.user',
                ],
            ],

            // SAML
            'milex.security.saml.credential_store' => [
                'class'     => \Milex\UserBundle\Security\SAML\Store\CredentialsStore::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    '%milex.saml_idp_entity_id%',
                ],
                'tag'       => 'lightsaml.own_credential_store',
            ],

            'milex.security.saml.trust_store' => [
                'class'     => \Milex\UserBundle\Security\SAML\Store\TrustOptionsStore::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    '%milex.saml_idp_entity_id%',
                ],
                'tag'       => 'lightsaml.trust_options_store',
            ],

            'milex.security.saml.entity_descriptor_store' => [
                'class'     => \Milex\UserBundle\Security\SAML\Store\EntityDescriptorStore::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
                'tag'       => 'lightsaml.idp_entity_store',
            ],

            'milex.security.saml.id_store' => [
                'class'     => \Milex\UserBundle\Security\SAML\Store\IdStore::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'lightsaml.system.time_provider',
                ],
            ],

            'milex.security.saml.username_mapper' => [
                'class'     => \Milex\UserBundle\Security\SAML\User\UserMapper::class,
                'arguments' => [
                    [
                        'email'     => '%milex.saml_idp_email_attribute%',
                        'username'  => '%milex.saml_idp_username_attribute%',
                        'firstname' => '%milex.saml_idp_firstname_attribute%',
                        'lastname'  => '%milex.saml_idp_lastname_attribute%',
                    ],
                ],
            ],

            'milex.security.saml.user_creator' => [
                'class'     => \Milex\UserBundle\Security\SAML\User\UserCreator::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'milex.security.saml.username_mapper',
                    'milex.user.model.user',
                    'security.password_encoder',
                    '%milex.saml_idp_default_role%',
                ],
            ],
        ],
        'models' => [
            'milex.user.model.role' => [
                'class' => 'Milex\UserBundle\Model\RoleModel',
            ],
            'milex.user.model.user' => [
                'class'     => 'Milex\UserBundle\Model\UserModel',
                'arguments' => [
                    'milex.helper.mailer',
                    'milex.user.model.user_token_service',
                ],
            ],
            'milex.user.model.user_token_service' => [
                'class'     => \Milex\UserBundle\Model\UserToken\UserTokenService::class,
                'arguments' => [
                    'milex.helper.random',
                    'milex.user.repository.user_token',
                ],
            ],
        ],
        'repositories' => [
            'milex.user.repository.user_token' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\UserBundle\Entity\UserToken::class,
                ],
            ],
        ],
        'fixtures' => [
            'milex.user.fixture.role' => [
                'class'     => \Milex\UserBundle\DataFixtures\ORM\LoadRoleData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.user.model.role'],
            ],
            'milex.user.fixture.user' => [
                'class'     => \Milex\UserBundle\DataFixtures\ORM\LoadUserData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['security.password_encoder'],
            ],
        ],
    ],
    'parameters' => [
        'saml_idp_metadata'            => '',
        'saml_idp_entity_id'           => '',
        'saml_idp_own_certificate'     => '',
        'saml_idp_own_private_key'     => '',
        'saml_idp_own_password'        => '',
        'saml_idp_email_attribute'     => '',
        'saml_idp_username_attribute'  => '',
        'saml_idp_firstname_attribute' => '',
        'saml_idp_lastname_attribute'  => '',
        'saml_idp_default_role'        => '',
    ],
];
