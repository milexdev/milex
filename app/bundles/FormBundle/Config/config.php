<?php

use Milex\FormBundle\Event\Service\FieldValueTransformer;
use Milex\FormBundle\EventListener\CampaignSubscriber;
use Milex\FormBundle\EventListener\DashboardSubscriber;
use Milex\FormBundle\EventListener\EmailSubscriber;
use Milex\FormBundle\EventListener\FormSubscriber;
use Milex\FormBundle\EventListener\FormValidationSubscriber;
use Milex\FormBundle\EventListener\LeadSubscriber;
use Milex\FormBundle\EventListener\PageSubscriber;
use Milex\FormBundle\EventListener\PointSubscriber;
use Milex\FormBundle\EventListener\ReportSubscriber;
use Milex\FormBundle\EventListener\SearchSubscriber;
use Milex\FormBundle\EventListener\StatsSubscriber;
use Milex\FormBundle\EventListener\WebhookSubscriber;
use Milex\FormBundle\Form\Type\CampaignEventFormFieldValueType;
use Milex\FormBundle\Form\Type\FieldType;
use Milex\FormBundle\Form\Type\FormFieldFileType;
use Milex\FormBundle\Form\Type\FormFieldPageBreakType;
use Milex\FormBundle\Form\Type\FormFieldTelType;
use Milex\FormBundle\Form\Type\FormListType;
use Milex\FormBundle\Form\Type\FormType;
use Milex\FormBundle\Form\Type\SubmitActionEmailType;
use Milex\FormBundle\Form\Type\SubmitActionRepostType;
use Milex\FormBundle\Helper\FormFieldHelper;
use Milex\FormBundle\Helper\FormUploader;
use Milex\FormBundle\Helper\TokenHelper;
use Milex\FormBundle\Model\ActionModel;
use Milex\FormBundle\Model\FieldModel;
use Milex\FormBundle\Model\FormModel;
use Milex\FormBundle\Model\SubmissionModel;
use Milex\FormBundle\Model\SubmissionResultLoader;
use Milex\FormBundle\Validator\Constraint\FileExtensionConstraintValidator;
use Milex\FormBundle\Validator\UploadFieldValidator;

return [
    'routes' => [
        'main' => [
            'milex_formaction_action' => [
                'path'       => '/forms/action/{objectAction}/{objectId}',
                'controller' => 'MilexFormBundle:Action:execute',
            ],
            'milex_formfield_action' => [
                'path'       => '/forms/field/{objectAction}/{objectId}',
                'controller' => 'MilexFormBundle:Field:execute',
            ],
            'milex_form_index' => [
                'path'       => '/forms/{page}',
                'controller' => 'MilexFormBundle:Form:index',
            ],
            'milex_form_results' => [
                'path'       => '/forms/results/{objectId}/{page}',
                'controller' => 'MilexFormBundle:Result:index',
            ],
            'milex_form_export' => [
                'path'       => '/forms/results/{objectId}/export/{format}',
                'controller' => 'MilexFormBundle:Result:export',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
            'milex_form_results_action' => [
                'path'       => '/forms/results/{formId}/{objectAction}/{objectId}',
                'controller' => 'MilexFormBundle:Result:execute',
                'defaults'   => [
                    'objectId' => 0,
                ],
            ],
            'milex_form_action' => [
                'path'       => '/forms/{objectAction}/{objectId}',
                'controller' => 'MilexFormBundle:Form:execute',
            ],
        ],
        'api' => [
            'milex_api_formstandard' => [
                'standard_entity' => true,
                'name'            => 'forms',
                'path'            => '/forms',
                'controller'      => 'MilexFormBundle:Api\FormApi',
            ],
            'milex_api_formresults' => [
                'path'       => '/forms/{formId}/submissions',
                'controller' => 'MilexFormBundle:Api\SubmissionApi:getEntities',
            ],
            'milex_api_formresult' => [
                'path'       => '/forms/{formId}/submissions/{submissionId}',
                'controller' => 'MilexFormBundle:Api\SubmissionApi:getEntity',
            ],
            'milex_api_contactformresults' => [
                'path'       => '/forms/{formId}/submissions/contact/{contactId}',
                'controller' => 'MilexFormBundle:Api\SubmissionApi:getEntitiesForContact',
            ],
            'milex_api_formdeletefields' => [
                'path'       => '/forms/{formId}/fields/delete',
                'controller' => 'MilexFormBundle:Api\FormApi:deleteFields',
                'method'     => 'DELETE',
            ],
            'milex_api_formdeleteactions' => [
                'path'       => '/forms/{formId}/actions/delete',
                'controller' => 'MilexFormBundle:Api\FormApi:deleteActions',
                'method'     => 'DELETE',
            ],
        ],
        'public' => [
            'milex_form_file_download' => [
                'path'       => '/forms/results/file/{submissionId}/{field}',
                'controller' => 'MilexFormBundle:Result:downloadFile',
            ],
            'milex_form_postresults' => [
                'path'       => '/form/submit',
                'controller' => 'MilexFormBundle:Public:submit',
            ],
            'milex_form_generateform' => [
                'path'       => '/form/generate.js',
                'controller' => 'MilexFormBundle:Public:generate',
            ],
            'milex_form_postmessage' => [
                'path'       => '/form/message',
                'controller' => 'MilexFormBundle:Public:message',
            ],
            'milex_form_preview' => [
                'path'       => '/form/{id}',
                'controller' => 'MilexFormBundle:Public:preview',
                'defaults'   => [
                    'id' => '0',
                ],
            ],
            'milex_form_embed' => [
                'path'       => '/form/embed/{id}',
                'controller' => 'MilexFormBundle:Public:embed',
            ],
            'milex_form_postresults_ajax' => [
                'path'       => '/form/submit/ajax',
                'controller' => 'MilexFormBundle:Ajax:submit',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'items' => [
                'milex.form.forms' => [
                    'route'    => 'milex_form_index',
                    'access'   => ['form:forms:viewown', 'form:forms:viewother'],
                    'parent'   => 'milex.core.components',
                    'priority' => 200,
                ],
            ],
        ],
    ],

    'categories' => [
        'form' => null,
    ],

    'services' => [
        'events' => [
            'milex.core.configbundle.subscriber.form' => [
                'class'     => \Milex\FormBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.form.subscriber' => [
                'class'     => FormSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                    'milex.helper.mailer',
                    'milex.helper.core_parameters',
                    'translator',
                    'router',
                ],
            ],
            'milex.form.validation.subscriber' => [
                'class'     => FormValidationSubscriber::class,
                'arguments' => [
                    'translator',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.form.pagebundle.subscriber' => [
                'class'     => PageSubscriber::class,
                'arguments' => [
                    'milex.form.model.form',
                    'milex.helper.token_builder.factory',
                    'translator',
                    'milex.security',
                ],
            ],
            'milex.form.pointbundle.subscriber' => [
                'class'     => PointSubscriber::class,
                'arguments' => [
                    'milex.point.model.point',
                ],
            ],
            'milex.form.reportbundle.subscriber' => [
                'class'     => ReportSubscriber::class,
                'arguments' => [
                    'milex.lead.model.company_report_data',
                    'milex.form.repository.submission',
                ],
            ],
            'milex.form.campaignbundle.subscriber' => [
                'class'     => CampaignSubscriber::class,
                'arguments' => [
                    'milex.form.model.form',
                    'milex.form.model.submission',
                    'milex.campaign.executioner.realtime',
                    'milex.helper.form.field_helper',
                ],
            ],
            'milex.form.leadbundle.subscriber' => [
                'class'     => LeadSubscriber::class,
                'arguments' => [
                    'milex.form.model.form',
                    'milex.page.model.page',
                    'milex.form.repository.submission',
                    'translator',
                    'router',
                ],
            ],
            'milex.form.emailbundle.subscriber' => [
                'class' => EmailSubscriber::class,
            ],
            'milex.form.search.subscriber' => [
                'class'     => SearchSubscriber::class,
                'arguments' => [
                    'milex.helper.user',
                    'milex.form.model.form',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.form.webhook.subscriber' => [
                'class'     => WebhookSubscriber::class,
                'arguments' => [
                    'milex.webhook.model.webhook',
                ],
            ],
            'milex.form.dashboard.subscriber' => [
                'class'     => DashboardSubscriber::class,
                'arguments' => [
                    'milex.form.model.submission',
                    'milex.form.model.form',
                    'router',
                ],
            ],
            'milex.form.stats.subscriber' => [
                'class'     => StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.form.subscriber.determine_winner' => [
                'class'     => \Milex\FormBundle\EventListener\DetermineWinnerSubscriber::class,
                'arguments' => [
                    'milex.form.repository.submission',
                    'translator',
                ],
            ],
            'milex.form.conditional.subscriber' => [
                'class'     => \Milex\FormBundle\EventListener\FormConditionalSubscriber::class,
                'arguments' => [
                    'milex.form.model.form',
                    'milex.form.model.field',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.formconfig' => [
                'class'     => \Milex\FormBundle\Form\Type\ConfigFormType::class,
                    'alias' => 'formconfig',
            ],
            'milex.form.type.form' => [
                'class'     => FormType::class,
                'arguments' => [
                    'milex.security',
                ],
            ],
            'milex.form.type.field' => [
                'class'       => FieldType::class,
                'arguments'   => [
                    'translator',
                ],
                'methodCalls' => [
                    'setFieldModel' => ['milex.form.model.field'],
                    'setFormModel'  => ['milex.form.model.form'],
                ],
            ],
            'milex.form.type.field_propertypagebreak' => [
                'class'     => FormFieldPageBreakType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.field_propertytel' => [
                'class'     => FormFieldTelType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.field_propertyemail' => [
                'class'     => \Milex\FormBundle\Form\Type\FormFieldEmailType::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.form.type.field_propertyfile' => [
                'class'     => FormFieldFileType::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                    'translator',
                ],
            ],
            'milex.form.type.form_list' => [
                'class'     => FormListType::class,
                'arguments' => [
                    'milex.security',
                    'milex.form.model.form',
                    'milex.helper.user',
                ],
            ],
            'milex.form.type.campaignevent_form_field_value' => [
                'class'     => CampaignEventFormFieldValueType::class,
                'arguments' => [
                    'milex.form.model.form',
                ],
            ],
            'milex.form.type.form_submitaction_sendemail' => [
                'class'       => SubmitActionEmailType::class,
                'arguments'   => [
                    'translator',
                    'milex.helper.core_parameters',
                ],
                'methodCalls' => [
                    'setFieldModel' => ['milex.form.model.field'],
                    'setFormModel'  => ['milex.form.model.form'],
                ],
            ],
            'milex.form.type.form_submitaction_repost' => [
                'class'       => SubmitActionRepostType::class,
                'methodCalls' => [
                    'setFieldModel' => ['milex.form.model.field'],
                    'setFormModel'  => ['milex.form.model.form'],
                ],
            ],
            'milex.form.type.field.conditional' => [
                'class'       => \Milex\FormBundle\Form\Type\FormFieldConditionType::class,
                'arguments'   => [
                    'milex.form.model.field',
                    'milex.form.helper.properties.accessor',
                ],
            ],
        ],
        'models' => [
            'milex.form.model.action' => [
                'class' => ActionModel::class,
            ],
            'milex.form.model.field' => [
                'class'     => FieldModel::class,
                'arguments' => [
                    'milex.lead.model.field',
                ],
            ],
            'milex.form.model.form' => [
                'class'     => FormModel::class,
                'arguments' => [
                    'request_stack',
                    'milex.helper.templating',
                    'milex.helper.theme',
                    'milex.form.model.action',
                    'milex.form.model.field',
                    'milex.helper.form.field_helper',
                    'milex.lead.model.field',
                    'milex.form.helper.form_uploader',
                    'milex.tracker.contact',
                    'milex.schema.helper.column',
                    'milex.schema.helper.table',
                ],
            ],
            'milex.form.model.submission' => [
                'class'     => SubmissionModel::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.helper.templating',
                    'milex.form.model.form',
                    'milex.page.model.page',
                    'milex.lead.model.lead',
                    'milex.campaign.model.campaign',
                    'milex.campaign.membership.manager',
                    'milex.lead.model.field',
                    'milex.lead.model.company',
                    'milex.helper.form.field_helper',
                    'milex.form.validator.upload_field_validator',
                    'milex.form.helper.form_uploader',
                    'milex.lead.service.device_tracking_service',
                    'milex.form.service.field.value.transformer',
                    'milex.helper.template.date',
                    'milex.tracker.contact',
                ],
            ],
            'milex.form.model.submission_result_loader' => [
                'class'     => SubmissionResultLoader::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
        ],
        'repositories' => [
            'milex.form.repository.form' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => \Milex\FormBundle\Entity\Form::class,
            ],
            'milex.form.repository.submission' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => \Milex\FormBundle\Entity\Submission::class,
            ],
        ],
        'other' => [
            'milex.helper.form.field_helper' => [
                'class'     => FormFieldHelper::class,
                'arguments' => [
                    'translator',
                    'validator',
                ],
            ],
            'milex.form.helper.form_uploader' => [
                'class'     => FormUploader::class,
                'arguments' => [
                    'milex.helper.file_uploader',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.form.helper.token' => [
                'class'     => TokenHelper::class,
                'arguments' => [
                    'milex.form.model.form',
                    'milex.security',
                ],
            ],
            'milex.form.service.field.value.transformer' => [
                'class'     => FieldValueTransformer::class,
                'arguments' => [
                    'router',
                ],
            ],
            'milex.form.helper.properties.accessor' => [
                'class'     => \Milex\FormBundle\Helper\PropertiesAccessor::class,
                'arguments' => [
                    'milex.form.model.form',
                ],
            ],
        ],
        'validator' => [
            'milex.form.validator.upload_field_validator' => [
                'class'     => UploadFieldValidator::class,
                'arguments' => [
                    'milex.core.validator.file_upload',
                ],
            ],
            'milex.form.validator.constraint.file_extension_constraint_validator' => [
                'class'     => FileExtensionConstraintValidator::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
                'tags' => [
                    'name'  => 'validator.constraint_validator',
                    'alias' => 'file_extension_constraint_validator',
                ],
            ],
        ],
        'fixtures' => [
            'milex.form.fixture.form' => [
                'class'     => \Milex\FormBundle\DataFixtures\ORM\LoadFormData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.form.model.form', 'milex.form.model.field', 'milex.form.model.action'],
            ],
            'milex.form.fixture.form_result' => [
                'class'     => \Milex\FormBundle\DataFixtures\ORM\LoadFormResultData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.page.model.page', 'milex.form.model.submission'],
            ],
        ],
    ],

    'parameters' => [
        'form_upload_dir'        => '%kernel.root_dir%/../media/files/form',
        'blacklisted_extensions' => ['php', 'sh'],
        'do_not_submit_emails'   => [],
    ],
];
