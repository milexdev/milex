<?php

return [
    'routes' => [
        'main' => [
            'milex_asset_index' => [
                'path'       => '/assets/{page}',
                'controller' => 'MilexAssetBundle:Asset:index',
            ],
            'milex_asset_remote' => [
                'path'       => '/assets/remote',
                'controller' => 'MilexAssetBundle:Asset:remote',
            ],
            'milex_asset_action' => [
                'path'       => '/assets/{objectAction}/{objectId}',
                'controller' => 'MilexAssetBundle:Asset:execute',
            ],
        ],
        'api' => [
            'milex_api_assetsstandard' => [
                'standard_entity' => true,
                'name'            => 'assets',
                'path'            => '/assets',
                'controller'      => 'MilexAssetBundle:Api\AssetApi',
            ],
        ],
        'public' => [
            'milex_asset_download' => [
                'path'       => '/asset/{slug}',
                'controller' => 'MilexAssetBundle:Public:download',
                'defaults'   => [
                    'slug' => '',
                ],
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'items' => [
                'milex.asset.assets' => [
                    'route'    => 'milex_asset_index',
                    'access'   => ['asset:assets:viewown', 'asset:assets:viewother'],
                    'parent'   => 'milex.core.components',
                    'priority' => 300,
                ],
            ],
        ],
    ],

    'categories' => [
        'asset' => null,
    ],

    'services' => [
        'permissions' => [
            'milex.asset.permissions' => [
                'class'     => \Milex\AssetBundle\Security\Permissions\AssetPermissions::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
        ],
        'events' => [
            'milex.asset.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\AssetSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.asset.pointbundle.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\PointSubscriber::class,
                'arguments' => [
                    'milex.point.model.point',
                ],
            ],
            'milex.asset.formbundle.subscriber' => [
                'class'     => Milex\AssetBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'milex.asset.model.asset',
                    'translator',
                    'milex.helper.template.analytics',
                    'templating.helper.assets',
                    'milex.helper.theme',
                    'milex.helper.templating',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.asset.campaignbundle.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.campaign.executioner.realtime',
                ],
            ],
            'milex.asset.reportbundle.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\ReportSubscriber::class,
                'arguments' => [
                    'milex.lead.model.company_report_data',
                    'milex.asset.repository.download',
                ],
            ],
            'milex.asset.builder.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\BuilderSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'milex.asset.helper.token',
                    'milex.tracker.contact',
                    'milex.helper.token_builder.factory',
                ],
            ],
            'milex.asset.leadbundle.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.asset.model.asset',
                    'translator',
                    'router',
                    'milex.asset.repository.download',
                ],
            ],
            'milex.asset.pagebundle.subscriber' => [
                'class' => \Milex\AssetBundle\EventListener\PageSubscriber::class,
            ],
            'milex.asset.emailbundle.subscriber' => [
                'class' => \Milex\AssetBundle\EventListener\EmailSubscriber::class,
            ],
            'milex.asset.configbundle.subscriber' => [
                'class' => \Milex\AssetBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.asset.search.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.asset.model.asset',
                    'milex.security',
                    'milex.helper.user',
                    'milex.helper.templating',
                ],
            ],
            'milex.asset.stats.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'oneup_uploader.pre_upload' => [
                'class'     => \Milex\AssetBundle\EventListener\UploadSubscriber::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'milex.asset.model.asset',
                    'milex.core.validator.file_upload',
                ],
            ],
            'milex.asset.dashboard.subscriber' => [
                'class'     => \Milex\AssetBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'milex.asset.model.asset',
                    'router',
                ],
            ],
            'milex.asset.subscriber.determine_winner' => [
                'class'     => \Milex\AssetBundle\EventListener\DetermineWinnerSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'translator',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.asset' => [
                'class'     => \Milex\AssetBundle\Form\Type\AssetType::class,
                'arguments' => [
                    'translator',
                    'milex.asset.model.asset',
                ],
            ],
            'milex.form.type.pointaction_assetdownload' => [
                'class' => \Milex\AssetBundle\Form\Type\PointActionAssetDownloadType::class,
            ],
            'milex.form.type.campaignevent_assetdownload' => [
                'class' => \Milex\AssetBundle\Form\Type\CampaignEventAssetDownloadType::class,
            ],
            'milex.form.type.formsubmit_assetdownload' => [
                'class' => \Milex\AssetBundle\Form\Type\FormSubmitActionDownloadFileType::class,
            ],
            'milex.form.type.assetlist' => [
                'class'     => \Milex\AssetBundle\Form\Type\AssetListType::class,
                'arguments' => [
                    'milex.security',
                    'milex.asset.model.asset',
                    'milex.helper.user',
                ],
            ],
            'milex.form.type.assetconfig' => [
                'class' => \Milex\AssetBundle\Form\Type\ConfigType::class,
            ],
        ],
        'others' => [
            'milex.asset.upload.error.handler' => [
                'class'     => \Milex\AssetBundle\ErrorHandler\DropzoneErrorHandler::class,
                'arguments' => 'milex.factory',
            ],
            // Override the DropzoneController
            'oneup_uploader.controller.dropzone.class' => \Milex\AssetBundle\Controller\UploadController::class,
            'milex.asset.helper.token'                => [
                'class'     => \Milex\AssetBundle\Helper\TokenHelper::class,
                'arguments' => 'milex.asset.model.asset',
            ],
        ],
        'models' => [
            'milex.asset.model.asset' => [
                'class'     => \Milex\AssetBundle\Model\AssetModel::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.category.model.category',
                    'request_stack',
                    'milex.helper.ip_lookup',
                    'milex.helper.core_parameters',
                    'milex.lead.service.device_creator_service',
                    'milex.lead.factory.device_detector_factory',
                    'milex.lead.service.device_tracking_service',
                    'milex.tracker.contact',
                ],
            ],
        ],
        'fixtures' => [
            'milex.asset.fixture.asset' => [
                'class'     => \Milex\AssetBundle\DataFixtures\ORM\LoadAssetData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
            ],
        ],
        'repositories' => [
            'milex.asset.repository.download' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => \Milex\AssetBundle\Entity\Download::class,
            ],
        ],
    ],

    'parameters' => [
        'upload_dir'         => '%kernel.root_dir%/../media/files',
        'max_size'           => '6',
        'allowed_extensions' => ['csv', 'doc', 'docx', 'epub', 'gif', 'jpg', 'jpeg', 'mpg', 'mpeg', 'mp3', 'odt', 'odp', 'ods', 'pdf', 'png', 'ppt', 'pptx', 'tif', 'tiff', 'txt', 'xls', 'xlsx', 'wav'],
    ],
];
