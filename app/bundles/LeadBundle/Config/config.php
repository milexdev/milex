<?php

return [
    'routes' => [
        'main' => [
            'milex_plugin_timeline_index' => [
                'path'         => '/plugin/{integration}/timeline/{page}',
                'controller'   => 'MilexLeadBundle:Timeline:pluginIndex',
                'requirements' => [
                    'integration' => '.+',
                ],
            ],
            'milex_plugin_timeline_view' => [
                'path'         => '/plugin/{integration}/timeline/view/{leadId}/{page}',
                'controller'   => 'MilexLeadBundle:Timeline:pluginView',
                'requirements' => [
                    'integration' => '.+',
                    'leadId'      => '\d+',
                ],
            ],
            'milex_segment_batch_contact_set' => [
                'path'       => '/segments/batch/contact/set',
                'controller' => 'MilexLeadBundle:BatchSegment:set',
            ],
            'milex_segment_batch_contact_view' => [
                'path'       => '/segments/batch/contact/view',
                'controller' => 'MilexLeadBundle:BatchSegment:index',
            ],
            'milex_segment_index' => [
                'path'       => '/segments/{page}',
                'controller' => 'MilexLeadBundle:List:index',
            ],
            'milex_segment_action' => [
                'path'       => '/segments/{objectAction}/{objectId}',
                'controller' => 'MilexLeadBundle:List:execute',
            ],
            'milex_contactfield_index' => [
                'path'       => '/contacts/fields/{page}',
                'controller' => 'MilexLeadBundle:Field:index',
            ],
            'milex_contactfield_action' => [
                'path'       => '/contacts/fields/{objectAction}/{objectId}',
                'controller' => 'MilexLeadBundle:Field:execute',
            ],
            'milex_contact_index' => [
                'path'       => '/contacts/{page}',
                'controller' => 'MilexLeadBundle:Lead:index',
            ],
            'milex_contactnote_index' => [
                'path'       => '/contacts/notes/{leadId}/{page}',
                'controller' => 'MilexLeadBundle:Note:index',
                'defaults'   => [
                    'leadId' => 0,
                ],
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'milex_contactnote_action' => [
                'path'         => '/contacts/notes/{leadId}/{objectAction}/{objectId}',
                'controller'   => 'MilexLeadBundle:Note:executeNote',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'milex_contacttimeline_action' => [
                'path'         => '/contacts/timeline/{leadId}/{page}',
                'controller'   => 'MilexLeadBundle:Timeline:index',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'milex_contact_timeline_export_action' => [
                'path'         => '/contacts/timeline/batchExport/{leadId}',
                'controller'   => 'MilexLeadBundle:Timeline:batchExport',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'milex_contact_auditlog_action' => [
                'path'         => '/contacts/auditlog/{leadId}/{page}',
                'controller'   => 'MilexLeadBundle:Auditlog:index',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'milex_contact_auditlog_export_action' => [
                'path'         => '/contacts/auditlog/batchExport/{leadId}',
                'controller'   => 'MilexLeadBundle:Auditlog:batchExport',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'milex_contact_export_action' => [
                'path'         => '/contacts/contact/export/{contactId}',
                'controller'   => 'MilexLeadBundle:Lead:contactExport',
                'requirements' => [
                    'contactId' => '\d+',
                ],
            ],
            'milex_import_index' => [
                'path'       => '/{object}/import/{page}',
                'controller' => 'MilexLeadBundle:Import:index',
            ],
            'milex_import_action' => [
                'path'       => '/{object}/import/{objectAction}/{objectId}',
                'controller' => 'MilexLeadBundle:Import:execute',
            ],
            'milex_contact_action' => [
                'path'       => '/contacts/{objectAction}/{objectId}',
                'controller' => 'MilexLeadBundle:Lead:execute',
            ],
            'milex_company_index' => [
                'path'       => '/companies/{page}',
                'controller' => 'MilexLeadBundle:Company:index',
            ],
            'milex_company_contacts_list' => [
                'path'         => '/company/{objectId}/contacts/{page}',
                'controller'   => 'MilexLeadBundle:Company:contactsList',
                'requirements' => [
                    'objectId' => '\d+',
                ],
            ],
            'milex_company_action' => [
                'path'       => '/companies/{objectAction}/{objectId}',
                'controller' => 'MilexLeadBundle:Company:execute',
            ],
            'milex_company_export_action' => [
                'path'         => '/companies/company/export/{companyId}',
                'controller'   => 'MilexLeadBundle:Company:companyExport',
                'requirements' => [
                    'companyId' => '\d+',
                ],
            ],
            'milex_segment_contacts' => [
                'path'       => '/segment/view/{objectId}/contact/{page}',
                'controller' => 'MilexLeadBundle:List:contacts',
            ],
        ],
        'api' => [
            'milex_api_contactsstandard' => [
                'standard_entity' => true,
                'name'            => 'contacts',
                'path'            => '/contacts',
                'controller'      => 'MilexLeadBundle:Api\LeadApi',
            ],
            'milex_api_dncaddcontact' => [
                'path'       => '/contacts/{id}/dnc/{channel}/add',
                'controller' => 'MilexLeadBundle:Api\LeadApi:addDnc',
                'method'     => 'POST',
                'defaults'   => [
                    'channel' => 'email',
                ],
            ],
            'milex_api_dncremovecontact' => [
                'path'       => '/contacts/{id}/dnc/{channel}/remove',
                'controller' => 'MilexLeadBundle:Api\LeadApi:removeDnc',
                'method'     => 'POST',
            ],
            'milex_api_getcontactevents' => [
                'path'       => '/contacts/{id}/activity',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getActivity',
            ],
            'milex_api_getcontactsevents' => [
                'path'       => '/contacts/activity',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getAllActivity',
            ],
            'milex_api_getcontactnotes' => [
                'path'       => '/contacts/{id}/notes',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getNotes',
            ],
            'milex_api_getcontactdevices' => [
                'path'       => '/contacts/{id}/devices',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getDevices',
            ],
            'milex_api_getcontactcampaigns' => [
                'path'       => '/contacts/{id}/campaigns',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getCampaigns',
            ],
            'milex_api_getcontactssegments' => [
                'path'       => '/contacts/{id}/segments',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getLists',
            ],
            'milex_api_getcontactscompanies' => [
                'path'       => '/contacts/{id}/companies',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getCompanies',
            ],
            'milex_api_utmcreateevent' => [
                'path'       => '/contacts/{id}/utm/add',
                'controller' => 'MilexLeadBundle:Api\LeadApi:addUtmTags',
                'method'     => 'POST',
            ],
            'milex_api_utmremoveevent' => [
                'path'       => '/contacts/{id}/utm/{utmid}/remove',
                'controller' => 'MilexLeadBundle:Api\LeadApi:removeUtmTags',
                'method'     => 'POST',
            ],
            'milex_api_getcontactowners' => [
                'path'       => '/contacts/list/owners',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getOwners',
            ],
            'milex_api_getcontactfields' => [
                'path'       => '/contacts/list/fields',
                'controller' => 'MilexLeadBundle:Api\LeadApi:getFields',
            ],
            'milex_api_getcontactsegments' => [
                'path'       => '/contacts/list/segments',
                'controller' => 'MilexLeadBundle:Api\ListApi:getLists',
            ],
            'milex_api_segmentsstandard' => [
                'standard_entity' => true,
                'name'            => 'lists',
                'path'            => '/segments',
                'controller'      => 'MilexLeadBundle:Api\ListApi',
            ],
            'milex_api_segmentaddcontact' => [
                'path'       => '/segments/{id}/contact/{leadId}/add',
                'controller' => 'MilexLeadBundle:Api\ListApi:addLead',
                'method'     => 'POST',
            ],
            'milex_api_segmentaddcontacts' => [
                'path'       => '/segments/{id}/contacts/add',
                'controller' => 'MilexLeadBundle:Api\ListApi:addLeads',
                'method'     => 'POST',
            ],
            'milex_api_segmentremovecontact' => [
                'path'       => '/segments/{id}/contact/{leadId}/remove',
                'controller' => 'MilexLeadBundle:Api\ListApi:removeLead',
                'method'     => 'POST',
            ],
            'milex_api_companiesstandard' => [
                'standard_entity' => true,
                'name'            => 'companies',
                'path'            => '/companies',
                'controller'      => 'MilexLeadBundle:Api\CompanyApi',
            ],
            'milex_api_companyaddcontact' => [
                'path'       => '/companies/{companyId}/contact/{contactId}/add',
                'controller' => 'MilexLeadBundle:Api\CompanyApi:addContact',
                'method'     => 'POST',
            ],
            'milex_api_companyremovecontact' => [
                'path'       => '/companies/{companyId}/contact/{contactId}/remove',
                'controller' => 'MilexLeadBundle:Api\CompanyApi:removeContact',
                'method'     => 'POST',
            ],
            'milex_api_fieldsstandard' => [
                'standard_entity' => true,
                'name'            => 'fields',
                'path'            => '/fields/{object}',
                'controller'      => 'MilexLeadBundle:Api\FieldApi',
                'defaults'        => [
                    'object' => 'contact',
                ],
            ],
            'milex_api_notesstandard' => [
                'standard_entity' => true,
                'name'            => 'notes',
                'path'            => '/notes',
                'controller'      => 'MilexLeadBundle:Api\NoteApi',
            ],
            'milex_api_devicesstandard' => [
                'standard_entity' => true,
                'name'            => 'devices',
                'path'            => '/devices',
                'controller'      => 'MilexLeadBundle:Api\DeviceApi',
            ],
            'milex_api_tagsstandard' => [
                'standard_entity' => true,
                'name'            => 'tags',
                'path'            => '/tags',
                'controller'      => 'MilexLeadBundle:Api\TagApi',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'milex.lead.leads' => [
                    'iconClass' => 'fa-user',
                    'access'    => ['lead:leads:viewown', 'lead:leads:viewother'],
                    'route'     => 'milex_contact_index',
                    'priority'  => 80,
                ],
                'milex.companies.menu.index' => [
                    'route'     => 'milex_company_index',
                    'iconClass' => 'fa-building-o',
                    'access'    => ['lead:leads:viewother'],
                    'priority'  => 75,
                ],
                'milex.lead.list.menu.index' => [
                    'iconClass' => 'fa-pie-chart',
                    'access'    => ['lead:leads:viewown', 'lead:leads:viewother'],
                    'route'     => 'milex_segment_index',
                    'priority'  => 70,
                ],
            ],
        ],
        'admin' => [
            'priority' => 50,
            'items'    => [
                'milex.lead.field.menu.index' => [
                    'id'        => 'milex_lead_field',
                    'iconClass' => 'fa-list',
                    'route'     => 'milex_contactfield_index',
                    'access'    => 'lead:fields:full',
                ],
            ],
        ],
    ],
    'categories' => [
        'segment' => null,
    ],
    'services' => [
        'events' => [
            'milex.lead.subscriber' => [
                'class'     => Milex\LeadBundle\EventListener\LeadSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                    'milex.lead.event.dispatcher',
                    'milex.helper.template.dnc_reason',
                    'doctrine.orm.entity_manager',
                    'translator',
                    'router',
                ],
                'methodCalls' => [
                    'setModelFactory' => ['milex.model.factory'],
                ],
            ],
            'milex.lead.subscriber.company' => [
                'class'     => \Milex\LeadBundle\EventListener\CompanySubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.lead.emailbundle.subscriber' => [
                'class'     => Milex\LeadBundle\EventListener\EmailSubscriber::class,
                'arguments' => [
                    'milex.helper.token_builder.factory',
                ],
            ],
            'milex.lead.emailbundle.subscriber.owner' => [
                'class'     => \Milex\LeadBundle\EventListener\OwnerSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'translator',
                ],
            ],
            'milex.lead.formbundle.subscriber' => [
                'class'     => Milex\LeadBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'milex.email.model.email',
                    'milex.lead.model.lead',
                    'milex.tracker.contact',
                    'milex.helper.ip_lookup',
                ],
            ],
            'milex.lead.formbundle.contact.avatar.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\SetContactAvatarFormSubscriber::class,
                'arguments' => [
                    'milex.helper.template.avatar',
                    'milex.form.helper.form_uploader',
                    'milex.lead.model.lead',
                ],
            ],
            'milex.lead.campaignbundle.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\CampaignSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.lead.model.lead',
                    'milex.lead.model.field',
                    'milex.lead.model.list',
                    'milex.lead.model.company',
                    'milex.campaign.model.campaign',
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.lead.campaignbundle.action_delete_contacts.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\CampaignActionDeleteContactSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.campaign.helper.removed_contact_tracker',
                ],
            ],
            'milex.lead.campaignbundle.action_dnc.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\CampaignActionDNCSubscriber::class,
                'arguments' => [
                   'milex.lead.model.dnc',
                   'milex.lead.model.lead',
                ],
            ],
            'milex.lead.reportbundle.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\ReportSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.stage.model.stage',
                    'milex.campaign.model.campaign',
                    'milex.campaign.event_collector',
                    'milex.lead.model.company',
                    'milex.lead.model.company_report_data',
                    'milex.lead.reportbundle.fields_builder',
                    'translator',
                ],
            ],
            'milex.lead.reportbundle.segment_subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\SegmentReportSubscriber::class,
                'arguments' => [
                    'milex.lead.reportbundle.fields_builder',
                ],
            ],
            'milex.lead.reportbundle.report_dnc_subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\ReportDNCSubscriber::class,
                'arguments' => [
                    'milex.lead.reportbundle.fields_builder',
                    'milex.lead.model.company_report_data',
                    'translator',
                    'router',
                    'milex.channel.helper.channel_list',
                ],
            ],
            'milex.lead.reportbundle.segment_log_subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\SegmentLogReportSubscriber::class,
                'arguments' => [
                    'milex.lead.reportbundle.fields_builder',
                ],
            ],
            'milex.lead.reportbundle.report_utm_tag_subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\ReportUtmTagSubscriber::class,
                'arguments' => [
                    'milex.lead.reportbundle.fields_builder',
                    'milex.lead.model.company_report_data',
                ],
            ],
            'milex.lead.calendarbundle.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\CalendarSubscriber::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                    'translator',
                    'router',
                ],
            ],
            'milex.lead.pointbundle.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\PointSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                ],
            ],
            'milex.lead.search.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.email.repository.email',
                    'translator',
                    'milex.security',
                    'milex.helper.templating',
                ],
            ],
            'milex.webhook.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\WebhookSubscriber::class,
                'arguments' => [
                    'milex.webhook.model.webhook',
                ],
            ],
            'milex.lead.dashboard.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\DashboardSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.model.list',
                    'router',
                    'translator',
                ],
            ],
            'milex.lead.maintenance.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\MaintenanceSubscriber::class,
                'arguments' => [
                    'doctrine.dbal.default_connection',
                    'translator',
                ],
            ],
            'milex.lead.stats.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'milex.security',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.lead.button.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\ButtonSubscriber::class,
                'arguments' => [
                    'translator',
                    'router',
                ],
            ],
            'milex.lead.import.contact.subscriber' => [
                'class'     => Milex\LeadBundle\EventListener\ImportContactSubscriber::class,
                'arguments' => [
                    'milex.lead.field.field_list',
                    'milex.security',
                    'milex.lead.model.lead',
                    'translator',
                ],
            ],
            'milex.lead.import.company.subscriber' => [
                'class'     => Milex\LeadBundle\EventListener\ImportCompanySubscriber::class,
                'arguments' => [
                    'milex.lead.field.field_list',
                    'milex.security',
                    'milex.lead.model.company',
                    'translator',
                ],
            ],
            'milex.lead.import.subscriber' => [
                'class'     => Milex\LeadBundle\EventListener\ImportSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                ],
            ],
            'milex.lead.configbundle.subscriber' => [
                'class' => Milex\LeadBundle\EventListener\ConfigSubscriber::class,
            ],
            'milex.lead.timeline_events.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\TimelineEventLogSubscriber::class,
                'arguments' => [
                    'translator',
                    'milex.lead.repository.lead_event_log',
                ],
            ],
            'milex.lead.timeline_events.campaign.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\TimelineEventLogCampaignSubscriber::class,
                'arguments' => [
                    'milex.lead.repository.lead_event_log',
                    'milex.helper.user',
                    'translator',
                ],
            ],
            'milex.lead.timeline_events.segment.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\TimelineEventLogSegmentSubscriber::class,
                'arguments' => [
                    'milex.lead.repository.lead_event_log',
                    'milex.helper.user',
                    'translator',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.lead.subscriber.segment' => [
                'class'     => \Milex\LeadBundle\EventListener\SegmentSubscriber::class,
                'arguments' => [
                    'milex.helper.ip_lookup',
                    'milex.core.model.auditlog',
                    'milex.lead.model.list',
                    'translator',
                ],
            ],
            'milex.lead.serializer.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\SerializerSubscriber::class,
                'arguments' => [
                    'request_stack',
                ],
                'tag'          => 'jms_serializer.event_subscriber',
                'tagArguments' => [
                    'event' => \JMS\Serializer\EventDispatcher\Events::POST_SERIALIZE,
                ],
            ],
            'milex.lead.subscriber.donotcontact' => [
                'class'     => \Milex\LeadBundle\EventListener\DoNotContactSubscriber::class,
                'arguments' => [
                    'milex.lead.model.dnc',
                ],
            ],
            'milex.lead.subscriber.filterOperator' => [
                'class'     => \Milex\LeadBundle\EventListener\FilterOperatorSubscriber::class,
                'arguments' => [
                    'milex.lead.segment.operator_options',
                    'milex.lead.repository.field',
                    'milex.lead.provider.typeOperator',
                    'milex.lead.provider.fieldChoices',
                    'translator',
                ],
            ],
            'milex.lead.subscriber.typeOperator' => [
                'class'     => \Milex\LeadBundle\EventListener\TypeOperatorSubscriber::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.model.list',
                    'milex.campaign.model.campaign',
                    'milex.email.model.email',
                    'milex.stage.model.stage',
                    'milex.category.model.category',
                    'milex.asset.model.asset',
                    'translator',
                ],
            ],
            'milex.lead.subscriber.segmentOperatorQuery' => [
                'class'     => \Milex\LeadBundle\EventListener\SegmentOperatorQuerySubscriber::class,
            ],
            'milex.lead.generated_columns.subscriber' => [
                'class'     => \Milex\LeadBundle\EventListener\GeneratedColumnSubscriber::class,
                'arguments' => [
                    'milex.lead.model.list',
                    'translator',
                ],
            ],
        ],
        'forms' => [
            'milex.form.type.lead' => [
                'class'     => \Milex\LeadBundle\Form\Type\LeadType::class,
                'arguments' => [
                    'translator',
                    'milex.lead.model.company',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.form.type.leadlist' => [
                'class'     => \Milex\LeadBundle\Form\Type\ListType::class,
                'arguments' => [
                    'translator',
                    'milex.lead.model.list',
                ],
            ],
            'milex.form.type.leadlist_choices' => [
                'class'     => \Milex\LeadBundle\Form\Type\LeadListType::class,
                'arguments' => ['milex.lead.model.list'],
            ],
            'milex.form.type.leadlist_filter' => [
                'class'       => \Milex\LeadBundle\Form\Type\FilterType::class,
                'arguments'   => [
                    'milex.lead.provider.formAdjustments',
                    'milex.lead.model.list',
                ],
            ],
            'milex.form.type.leadfield' => [
                'class'     => \Milex\LeadBundle\Form\Type\FieldType::class,
                'arguments' => [
                    'doctrine.orm.default_entity_manager',
                    'translator',
                    'milex.lead.field.identifier_fields',
                ],
                'alias'     => 'leadfield',
            ],
            'milex.form.type.lead.submitaction.pointschange' => [
                'class'     => \Milex\LeadBundle\Form\Type\FormSubmitActionPointsChangeType::class,
            ],
            'milex.form.type.lead.submitaction.addutmtags' => [
                'class'     => \Milex\LeadBundle\Form\Type\ActionAddUtmTagsType::class,
            ],
            'milex.form.type.lead.submitaction.removedonotcontact' => [
                'class'     => \Milex\LeadBundle\Form\Type\ActionRemoveDoNotContact::class,
            ],
            'milex.form.type.leadpoints_action' => [
                'class' => \Milex\LeadBundle\Form\Type\PointActionType::class,
            ],
            'milex.form.type.leadlist_action' => [
                'class' => \Milex\LeadBundle\Form\Type\ListActionType::class,
            ],
            'milex.form.type.updatelead_action' => [
                'class'     => \Milex\LeadBundle\Form\Type\UpdateLeadActionType::class,
                'arguments' => ['milex.lead.model.field'],
            ],
            'milex.form.type.updatecompany_action' => [
                'class'     => Milex\LeadBundle\Form\Type\UpdateCompanyActionType::class,
                'arguments' => ['milex.lead.model.field'],
            ],
            'milex.form.type.leadnote' => [
                'class' => Milex\LeadBundle\Form\Type\NoteType::class,
            ],
            'milex.form.type.leaddevice' => [
                'class' => Milex\LeadBundle\Form\Type\DeviceType::class,
            ],
            'milex.form.type.lead_import' => [
                'class' => \Milex\LeadBundle\Form\Type\LeadImportType::class,
            ],
            'milex.form.type.lead_field_import' => [
                'class'     => \Milex\LeadBundle\Form\Type\LeadImportFieldType::class,
                'arguments' => ['translator', 'doctrine.orm.entity_manager'],
            ],
            'milex.form.type.lead_quickemail' => [
                'class'     => \Milex\LeadBundle\Form\Type\EmailType::class,
                'arguments' => ['milex.helper.user'],
            ],
            'milex.form.type.lead_tag' => [
                'class'     => \Milex\LeadBundle\Form\Type\TagType::class,
                'arguments' => ['doctrine.orm.entity_manager'],
            ],
            'milex.form.type.modify_lead_tags' => [
                'class'     => \Milex\LeadBundle\Form\Type\ModifyLeadTagsType::class,
                'arguments' => ['translator'],
            ],
            'milex.form.type.lead_entity_tag' => [
                'class' => \Milex\LeadBundle\Form\Type\TagEntityType::class,
            ],
            'milex.form.type.lead_batch' => [
                'class' => \Milex\LeadBundle\Form\Type\BatchType::class,
            ],
            'milex.form.type.lead_batch_dnc' => [
                'class' => \Milex\LeadBundle\Form\Type\DncType::class,
            ],
            'milex.form.type.lead_batch_stage' => [
                'class' => \Milex\LeadBundle\Form\Type\StageType::class,
            ],
            'milex.form.type.lead_batch_owner' => [
                'class' => \Milex\LeadBundle\Form\Type\OwnerType::class,
            ],
            'milex.form.type.lead_merge' => [
                'class' => \Milex\LeadBundle\Form\Type\MergeType::class,
            ],
            'milex.form.type.lead_contact_frequency_rules' => [
                'class'     => \Milex\LeadBundle\Form\Type\ContactFrequencyType::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.form.type.contact_channels' => [
                'class'     => \Milex\LeadBundle\Form\Type\ContactChannelsType::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.form.type.campaignevent_lead_field_value' => [
                'class'     => \Milex\LeadBundle\Form\Type\CampaignEventLeadFieldValueType::class,
                'arguments' => [
                    'translator',
                    'milex.lead.model.lead',
                    'milex.lead.model.field',
                ],
            ],
            'milex.form.type.campaignevent_lead_device' => [
                'class' => \Milex\LeadBundle\Form\Type\CampaignEventLeadDeviceType::class,
            ],
            'milex.form.type.campaignevent_lead_tags' => [
                'class'     => Milex\LeadBundle\Form\Type\CampaignEventLeadTagsType::class,
                'arguments' => ['translator'],
            ],
            'milex.form.type.campaignevent_lead_segments' => [
                'class' => \Milex\LeadBundle\Form\Type\CampaignEventLeadSegmentsType::class,
            ],
            'milex.form.type.campaignevent_lead_campaigns' => [
                'class'     => Milex\LeadBundle\Form\Type\CampaignEventLeadCampaignsType::class,
                'arguments' => ['milex.lead.model.list'],
            ],
            'milex.form.type.campaignevent_lead_owner' => [
                'class' => \Milex\LeadBundle\Form\Type\CampaignEventLeadOwnerType::class,
            ],
            'milex.form.type.lead_fields' => [
                'class'     => \Milex\LeadBundle\Form\Type\LeadFieldsType::class,
                'arguments' => ['milex.lead.model.field'],
            ],
            'milex.form.type.lead_columns' => [
                'class'     => \Milex\LeadBundle\Form\Type\ContactColumnsType::class,
                'arguments' => [
                    'milex.lead.columns.dictionary',
                ],
            ],
            'milex.form.type.lead_dashboard_leads_in_time_widget' => [
                'class' => \Milex\LeadBundle\Form\Type\DashboardLeadsInTimeWidgetType::class,
            ],
            'milex.form.type.lead_dashboard_leads_lifetime_widget' => [
                'class'     => \Milex\LeadBundle\Form\Type\DashboardLeadsLifetimeWidgetType::class,
                'arguments' => ['milex.lead.model.list', 'translator'],
            ],
            'milex.company.type.form' => [
                'class'     => \Milex\LeadBundle\Form\Type\CompanyType::class,
                'arguments' => ['doctrine.orm.entity_manager', 'router', 'translator'],
            ],
            'milex.company.campaign.action.type.form' => [
                'class'     => \Milex\LeadBundle\Form\Type\AddToCompanyActionType::class,
                'arguments' => ['router'],
            ],
            'milex.lead.events.changeowner.type.form' => [
                'class'     => 'Milex\LeadBundle\Form\Type\ChangeOwnerType',
                'arguments' => ['milex.user.model.user'],
            ],
            'milex.company.list.type.form' => [
                'class'     => \Milex\LeadBundle\Form\Type\CompanyListType::class,
                'arguments' => [
                    'milex.lead.model.company',
                    'milex.helper.user',
                    'translator',
                    'router',
                    'database_connection',
                ],
            ],
            'milex.form.type.lead_categories' => [
                'class'     => \Milex\LeadBundle\Form\Type\LeadCategoryType::class,
                'arguments' => ['milex.category.model.category'],
            ],
            'milex.company.merge.type.form' => [
                'class' => \Milex\LeadBundle\Form\Type\CompanyMergeType::class,
            ],
            'milex.form.type.company_change_score' => [
                'class' => \Milex\LeadBundle\Form\Type\CompanyChangeScoreActionType::class,
            ],
            'milex.form.type.config.form' => [
                'class' => Milex\LeadBundle\Form\Type\ConfigType::class,
            ],
            'milex.form.type.preference.channels' => [
                'class'     => \Milex\LeadBundle\Form\Type\PreferenceChannelsType::class,
                'arguments' => [
                    'milex.lead.model.lead',
                ],
            ],
            'milex.segment.config' => [
                'class' => \Milex\LeadBundle\Form\Type\SegmentConfigType::class,
            ],
        ],
        'other' => [
            'milex.lead.doctrine.subscriber' => [
                'class'     => 'Milex\LeadBundle\EventListener\DoctrineSubscriber',
                'tag'       => 'doctrine.event_subscriber',
                'arguments' => ['monolog.logger.milex'],
            ],
            'milex.validator.leadlistaccess' => [
                'class'     => \Milex\LeadBundle\Form\Validator\Constraints\LeadListAccessValidator::class,
                'arguments' => ['milex.lead.model.list'],
                'tag'       => 'validator.constraint_validator',
                'alias'     => 'leadlist_access',
            ],
            'milex.validator.emailaddress' => [
                'class'     => \Milex\LeadBundle\Form\Validator\Constraints\EmailAddressValidator::class,
                'arguments' => [
                    'milex.validator.email',
                ],
                'tag'       => 'validator.constraint_validator',
            ],
            \Milex\LeadBundle\Form\Validator\Constraints\FieldAliasKeywordValidator::class => [
                'class'     => \Milex\LeadBundle\Form\Validator\Constraints\FieldAliasKeywordValidator::class,
                'tag'       => 'validator.constraint_validator',
                'arguments' => [
                    'milex.lead.model.list',
                    'milex.helper.field.alias',
                    '@doctrine.orm.entity_manager',
                    'translator',
                    'milex.lead.repository.lead_segment_filter_descriptor',
                ],
            ],
            \Milex\CoreBundle\Form\Validator\Constraints\FileEncodingValidator::class => [
                'class'     => \Milex\CoreBundle\Form\Validator\Constraints\FileEncodingValidator::class,
                'tag'       => 'validator.constraint_validator',
                'arguments' => [
                    'milex.lead.model.list',
                    'milex.helper.field.alias',
                ],
            ],
            'milex.lead.constraint.alias' => [
                'class'     => \Milex\LeadBundle\Form\Validator\Constraints\UniqueUserAliasValidator::class,
                'arguments' => ['milex.lead.repository.lead_list', 'milex.helper.user'],
                'tag'       => 'validator.constraint_validator',
                'alias'     => 'uniqueleadlist',
            ],
            'milex.lead.validator.custom_field' => [
                'class'     => \Milex\LeadBundle\Validator\CustomFieldValidator::class,
                'arguments' => ['milex.lead.model.field', 'translator'],
            ],
            'milex.lead_list.constraint.in_use' => [
                'class'     => Milex\LeadBundle\Form\Validator\Constraints\SegmentInUseValidator::class,
                'arguments' => [
                    'milex.lead.model.list',
                ],
                'tag'       => 'validator.constraint_validator',
                'alias'     => 'segment_in_use',
            ],
            'milex.lead.event.dispatcher' => [
                'class'     => \Milex\LeadBundle\Helper\LeadChangeEventDispatcher::class,
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
            'milex.lead.merger' => [
                'class'     => \Milex\LeadBundle\Deduplicate\ContactMerger::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.repository.merged_records',
                    'event_dispatcher',
                    'monolog.logger.milex',
                ],
            ],
            'milex.lead.deduper' => [
                'class'     => \Milex\LeadBundle\Deduplicate\ContactDeduper::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'milex.lead.merger',
                    'milex.lead.repository.lead',
                ],
            ],
            'milex.company.deduper' => [
                'class'     => \Milex\LeadBundle\Deduplicate\CompanyDeduper::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'milex.lead.repository.company',
                ],
            ],
            'milex.lead.helper.primary_company' => [
                'class'     => \Milex\LeadBundle\Helper\PrimaryCompanyHelper::class,
                'arguments' => [
                    'milex.lead.repository.company_lead',
                ],
            ],
            'milex.lead.validator.length' => [
                'class'     => Milex\LeadBundle\Validator\Constraints\LengthValidator::class,
                'tag'       => 'validator.constraint_validator',
            ],
            'milex.lead.segment.stat.dependencies' => [
                'class'     => \Milex\LeadBundle\Segment\Stat\SegmentDependencies::class,
                'arguments' => [
                    'milex.email.model.email',
                    'milex.campaign.model.campaign',
                    'milex.form.model.action',
                    'milex.lead.model.list',
                    'milex.point.model.triggerevent',
                    'milex.report.model.report',
                ],
            ],
            'milex.lead.segment.stat.chart.query.factory' => [
                'class'     => \Milex\LeadBundle\Segment\Stat\SegmentChartQueryFactory::class,
                'arguments' => [
                ],
            ],
            'milex.lead.segment.stat.campaign.share' => [
                'class'     => \Milex\LeadBundle\Segment\Stat\SegmentCampaignShare::class,
                'arguments' => [
                    'milex.campaign.model.campaign',
                    'milex.helper.cache_storage',
                    '@doctrine.orm.entity_manager',
                ],
            ],
            'milex.lead.columns.dictionary' => [
                'class'     => \Milex\LeadBundle\Services\ContactColumnsDictionary::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'translator',
                    'milex.helper.core_parameters',
                ],
            ],
        ],
        'repositories' => [
            'milex.lead.repository.company' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\Company::class,
                ],
                'methodCalls' => [
                    'setUniqueIdentifiersOperator' => [
                        '%milex.company_unique_identifiers_operator%',
                    ],
                ],
            ],
            'milex.lead.repository.company_lead' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\CompanyLead::class,
                ],
            ],
            'milex.lead.repository.stages_lead_log' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\StagesChangeLog::class,
                ],
            ],
            'milex.lead.repository.dnc' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\DoNotContact::class,
                ],
            ],
            'milex.lead.repository.lead' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\Lead::class,
                ],
                'methodCalls' => [
                    'setUniqueIdentifiersOperator' => [
                        '%milex.contact_unique_identifiers_operator%',
                    ],
                    'setListLeadRepository' => [
                        '@milex.lead.repository.list_lead',
                    ],
                ],
            ],
            'milex.lead.repository.list_lead' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\ListLead::class,
                ],
            ],
            'milex.lead.repository.frequency_rule' => [
                'class'     => \Milex\LeadBundle\Entity\FrequencyRuleRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\FrequencyRule::class,
                ],
            ],
            'milex.lead.repository.lead_event_log' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\LeadEventLog::class,
                ],
            ],
            'milex.lead.repository.lead_device' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\LeadDevice::class,
                ],
            ],
            'milex.lead.repository.lead_list' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\LeadList::class,
                ],
            ],
            'milex.lead.repository.points_change_log' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\PointsChangeLog::class,
                ],
            ],
            'milex.lead.repository.merged_records' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\MergeRecord::class,
                ],
            ],
            'milex.lead.repository.field' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Milex\LeadBundle\Entity\LeadField::class,
                ],
            ],
            //  Segment Filter Query builders
            'milex.lead.query.builder.basic' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\BaseFilterQueryBuilder::class,
                'arguments' => ['milex.lead.model.random_parameter_name', 'event_dispatcher'],
            ],
            'milex.lead.query.builder.foreign.value' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\ForeignValueFilterQueryBuilder::class,
                'arguments' => ['milex.lead.model.random_parameter_name', 'event_dispatcher'],
            ],
            'milex.lead.query.builder.foreign.func' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\ForeignFuncFilterQueryBuilder::class,
                'arguments' => ['milex.lead.model.random_parameter_name', 'event_dispatcher'],
            ],
            'milex.lead.query.builder.special.dnc' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\DoNotContactFilterQueryBuilder::class,
                'arguments' => ['milex.lead.model.random_parameter_name', 'event_dispatcher'],
            ],
            'milex.lead.query.builder.special.integration' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\IntegrationCampaignFilterQueryBuilder::class,
                'arguments' => ['milex.lead.model.random_parameter_name', 'event_dispatcher'],
            ],
            'milex.lead.query.builder.special.sessions' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\SessionsFilterQueryBuilder::class,
                'arguments' => ['milex.lead.model.random_parameter_name', 'event_dispatcher'],
            ],
            'milex.lead.query.builder.complex_relation.value' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\ComplexRelationValueFilterQueryBuilder::class,
                'arguments' => ['milex.lead.model.random_parameter_name', 'event_dispatcher'],
            ],
            'milex.lead.query.builder.special.leadlist' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\SegmentReferenceFilterQueryBuilder::class,
                'arguments' => [
                    'milex.lead.model.random_parameter_name',
                    'milex.lead.repository.lead_segment_query_builder',
                    'doctrine.orm.entity_manager',
                    'milex.lead.model.lead_segment_filter_factory',
                    'event_dispatcher',
                ],
            ],
            'milex.lead.query.builder.channel_click.value' => [
                'class'     => \Milex\LeadBundle\Segment\Query\Filter\ChannelClickQueryBuilder::class,
                'arguments' => [
                    'milex.lead.model.random_parameter_name',
                    'event_dispatcher',
                ],
            ],
        ],
        'helpers' => [
            'milex.helper.template.avatar' => [
                'class'     => Milex\LeadBundle\Templating\Helper\AvatarHelper::class,
                'arguments' => [
                    'templating.helper.assets',
                    'milex.helper.paths',
                    'milex.helper.template.gravatar',
                    'milex.helper.template.default_avatar',
                ],
                'alias'     => 'lead_avatar',
            ],
            'milex.helper.template.default_avatar' => [
                'class'     => Milex\LeadBundle\Templating\Helper\DefaultAvatarHelper::class,
                'arguments' => [
                    'milex.helper.paths',
                    'templating.helper.assets',
                ],
                'alias'     => 'default_avatar',
            ],
            'milex.helper.field.alias' => [
                'class'     => \Milex\LeadBundle\Helper\FieldAliasHelper::class,
                'arguments' => ['milex.lead.model.field'],
            ],
            'milex.helper.template.dnc_reason' => [
                'class'     => Milex\LeadBundle\Templating\Helper\DncReasonHelper::class,
                'arguments' => ['translator'],
                'alias'     => 'lead_dnc_reason',
            ],
            'milex.helper.segment.count.cache' => [
                'class'     => \Milex\LeadBundle\Helper\SegmentCountCacheHelper::class,
                'arguments' => ['milex.helper.cache_storage'],
            ],
        ],
        'models' => [
            'milex.lead.model.lead' => [
                'class'     => \Milex\LeadBundle\Model\LeadModel::class,
                'arguments' => [
                    'request_stack',
                    'milex.helper.cookie',
                    'milex.helper.ip_lookup',
                    'milex.helper.paths',
                    'milex.helper.integration',
                    'milex.lead.model.field',
                    'milex.lead.model.list',
                    'form.factory',
                    'milex.lead.model.company',
                    'milex.category.model.category',
                    'milex.channel.helper.channel_list',
                    'milex.helper.core_parameters',
                    'milex.validator.email',
                    'milex.user.provider',
                    'milex.tracker.contact',
                    'milex.tracker.device',
                    'milex.lead.model.legacy_lead',
                    'milex.lead.model.ipaddress',
                ],
            ],

            // Deprecated support for circular dependency
            'milex.lead.model.legacy_lead' => [
                'class'     => \Milex\LeadBundle\Model\LegacyLeadModel::class,
                'arguments' => [
                    'service_container',
                ],
            ],
            'milex.lead.model.field' => [
                'class'     => \Milex\LeadBundle\Model\FieldModel::class,
                'arguments' => [
                    'milex.schema.helper.column',
                    'milex.lead.model.list',
                    'milex.lead.field.custom_field_column',
                    'milex.lead.field.dispatcher.field_save_dispatcher',
                    'milex.lead.repository.field',
                    'milex.lead.field.fields_with_unique_identifier',
                    'milex.lead.field.field_list',
                    'milex.lead.field.lead_field_saver',
                ],
            ],
            'milex.lead.model.list' => [
                'class'     => \Milex\LeadBundle\Model\ListModel::class,
                'arguments' => [
                    'milex.category.model.category',
                    'milex.helper.core_parameters',
                    'milex.lead.model.lead_segment_service',
                    'milex.lead.segment.stat.chart.query.factory',
                    'request_stack',
                    'milex.helper.segment.count.cache',
                ],
            ],
            'milex.lead.repository.lead_segment_filter_descriptor' => [
                'class'     => \Milex\LeadBundle\Services\ContactSegmentFilterDictionary::class,
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
            'milex.lead.repository.lead_segment_query_builder' => [
                'class'     => Milex\LeadBundle\Segment\Query\ContactSegmentQueryBuilder::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'milex.lead.model.random_parameter_name',
                    'event_dispatcher',
                ],
            ],
            'milex.lead.model.lead_segment_service' => [
                'class'     => \Milex\LeadBundle\Segment\ContactSegmentService::class,
                'arguments' => [
                    'milex.lead.model.lead_segment_filter_factory',
                    'milex.lead.repository.lead_segment_query_builder',
                    'monolog.logger.milex',
                ],
            ],
            'milex.lead.model.lead_segment_filter_factory' => [
                'class'     => \Milex\LeadBundle\Segment\ContactSegmentFilterFactory::class,
                'arguments' => [
                    'milex.lead.model.lead_segment_schema_cache',
                    '@service_container',
                    'milex.lead.model.lead_segment_decorator_factory',
                ],
            ],
            'milex.lead.model.lead_segment_schema_cache' => [
                'class'     => \Milex\LeadBundle\Segment\TableSchemaColumnsCache::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.lead.model.relative_date' => [
                'class'     => \Milex\LeadBundle\Segment\RelativeDate::class,
                'arguments' => [
                    'translator',
                ],
            ],
            'milex.lead.model.lead_segment_filter_operator' => [
                'class'     => \Milex\LeadBundle\Segment\ContactSegmentFilterOperator::class,
                'arguments' => [
                    'milex.lead.provider.fillterOperator',
                ],
            ],
            'milex.lead.model.lead_segment_decorator_factory' => [
                'class'     => \Milex\LeadBundle\Segment\Decorator\DecoratorFactory::class,
                'arguments' => [
                    'milex.lead.repository.lead_segment_filter_descriptor',
                    'milex.lead.model.lead_segment_decorator_base',
                    'milex.lead.model.lead_segment_decorator_custom_mapped',
                    'milex.lead.model.lead_segment.decorator.date.optionFactory',
                    'milex.lead.model.lead_segment_decorator_company',
                    'event_dispatcher',
                ],
            ],
            'milex.lead.model.lead_segment_decorator_base' => [
                'class'     => \Milex\LeadBundle\Segment\Decorator\BaseDecorator::class,
                'arguments' => [
                    'milex.lead.model.lead_segment_filter_operator',
                    'milex.lead.repository.lead_segment_filter_descriptor',
                ],
            ],
            'milex.lead.model.lead_segment_decorator_custom_mapped' => [
                'class'     => \Milex\LeadBundle\Segment\Decorator\CustomMappedDecorator::class,
                'arguments' => [
                    'milex.lead.model.lead_segment_filter_operator',
                    'milex.lead.repository.lead_segment_filter_descriptor',
                ],
            ],
            'milex.lead.model.lead_segment_decorator_company' => [
                'class'     => \Milex\LeadBundle\Segment\Decorator\CompanyDecorator::class,
                'arguments' => [
                    'milex.lead.model.lead_segment_filter_operator',
                    'milex.lead.repository.lead_segment_filter_descriptor',
                ],
            ],
            'milex.lead.model.lead_segment_decorator_date' => [
                'class'     => \Milex\LeadBundle\Segment\Decorator\DateDecorator::class,
                'arguments' => [
                    'milex.lead.model.lead_segment_filter_operator',
                    'milex.lead.repository.lead_segment_filter_descriptor',
                ],
            ],
            'milex.lead.model.lead_segment.decorator.date.optionFactory' => [
                'class'     => \Milex\LeadBundle\Segment\Decorator\Date\DateOptionFactory::class,
                'arguments' => [
                    'milex.lead.model.lead_segment_decorator_date',
                    'milex.lead.model.relative_date',
                    'milex.lead.model.lead_segment.timezoneResolver',
                ],
            ],
            'milex.lead.model.lead_segment.timezoneResolver' => [
                'class'     => \Milex\LeadBundle\Segment\Decorator\Date\TimezoneResolver::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.lead.provider.fillterOperator' => [
                'class'     => \Milex\LeadBundle\Provider\FilterOperatorProvider::class,
                'arguments' => [
                    'event_dispatcher',
                    'translator',
                ],
            ],
            'milex.lead.provider.typeOperator' => [
                'class'     => \Milex\LeadBundle\Provider\TypeOperatorProvider::class,
                'arguments' => [
                    'event_dispatcher',
                    'milex.lead.provider.fillterOperator',
                ],
            ],
            'milex.lead.provider.fieldChoices' => [
                'class'     => \Milex\LeadBundle\Provider\FieldChoicesProvider::class,
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
            'milex.lead.provider.formAdjustments' => [
                'class'     => \Milex\LeadBundle\Provider\FormAdjustmentsProvider::class,
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
            'milex.lead.model.random_parameter_name' => [
                'class'     => \Milex\LeadBundle\Segment\RandomParameterName::class,
            ],
            'milex.lead.segment.operator_options' => [
                'class'     => \Milex\LeadBundle\Segment\OperatorOptions::class,
            ],
            'milex.lead.model.note' => [
                'class' => 'Milex\LeadBundle\Model\NoteModel',
            ],
            'milex.lead.model.device' => [
                'class'     => Milex\LeadBundle\Model\DeviceModel::class,
                'arguments' => [
                    'milex.lead.repository.lead_device',
                ],
            ],
            'milex.lead.model.company' => [
                'class'     => 'Milex\LeadBundle\Model\CompanyModel',
                'arguments' => [
                    'milex.lead.model.field',
                    'session',
                    'milex.validator.email',
                    'milex.company.deduper',
                ],
            ],
            'milex.lead.model.import' => [
                'class'     => Milex\LeadBundle\Model\ImportModel::class,
                'arguments' => [
                    'milex.helper.paths',
                    'milex.lead.model.lead',
                    'milex.core.model.notification',
                    'milex.helper.core_parameters',
                    'milex.lead.model.company',
                ],
            ],
            'milex.lead.model.tag' => [
                'class' => \Milex\LeadBundle\Model\TagModel::class,
            ],
            'milex.lead.model.company_report_data' => [
                'class'     => \Milex\LeadBundle\Model\CompanyReportData::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'translator',
                ],
            ],
            'milex.lead.reportbundle.fields_builder' => [
                'class'     => \Milex\LeadBundle\Report\FieldsBuilder::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'milex.lead.model.list',
                    'milex.user.model.user',
                ],
            ],
            'milex.lead.model.dnc' => [
                'class'     => \Milex\LeadBundle\Model\DoNotContact::class,
                'arguments' => [
                    'milex.lead.model.lead',
                    'milex.lead.repository.dnc',
                ],
            ],
            'milex.lead.model.segment.action' => [
                'class'     => \Milex\LeadBundle\Model\SegmentActionModel::class,
                'arguments' => [
                    'milex.lead.model.lead',
                ],
            ],
            'milex.lead.factory.device_detector_factory' => [
                'class'     => \Milex\LeadBundle\Tracker\Factory\DeviceDetectorFactory\DeviceDetectorFactory::class,
                'arguments' => [
                  'milex.cache.provider',
                ],
            ],
            'milex.lead.service.contact_tracking_service' => [
                'class'     => \Milex\LeadBundle\Tracker\Service\ContactTrackingService\ContactTrackingService::class,
                'arguments' => [
                    'milex.helper.cookie',
                    'milex.lead.repository.lead_device',
                    'milex.lead.repository.lead',
                    'milex.lead.repository.merged_records',
                    'request_stack',
                ],
            ],
            'milex.lead.service.device_creator_service' => [
                'class' => \Milex\LeadBundle\Tracker\Service\DeviceCreatorService\DeviceCreatorService::class,
            ],
            'milex.lead.service.device_tracking_service' => [
                'class'     => \Milex\LeadBundle\Tracker\Service\DeviceTrackingService\DeviceTrackingService::class,
                'arguments' => [
                    'milex.helper.cookie',
                    'doctrine.orm.entity_manager',
                    'milex.lead.repository.lead_device',
                    'milex.helper.random',
                    'request_stack',
                    'milex.security',
                ],
            ],
            'milex.tracker.contact' => [
                'class'     => \Milex\LeadBundle\Tracker\ContactTracker::class,
                'arguments' => [
                    'milex.lead.repository.lead',
                    'milex.lead.service.contact_tracking_service',
                    'milex.tracker.device',
                    'milex.security',
                    'monolog.logger.milex',
                    'milex.helper.ip_lookup',
                    'request_stack',
                    'milex.helper.core_parameters',
                    'event_dispatcher',
                    'milex.lead.model.field',
                ],
            ],
            'milex.tracker.device' => [
                'class'     => \Milex\LeadBundle\Tracker\DeviceTracker::class,
                'arguments' => [
                    'milex.lead.service.device_creator_service',
                    'milex.lead.factory.device_detector_factory',
                    'milex.lead.service.device_tracking_service',
                    'monolog.logger.milex',
                ],
            ],
            'milex.lead.model.ipaddress' => [
                'class'     => Milex\LeadBundle\Model\IpAddressModel::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'monolog.logger.milex',
                ],
            ],
            'milex.lead.field.schema_definition' => [
                'class'     => Milex\LeadBundle\Field\SchemaDefinition::class,
            ],
            'milex.lead.field.custom_field_column' => [
                'class'     => Milex\LeadBundle\Field\CustomFieldColumn::class,
                'arguments' => [
                    'milex.schema.helper.column',
                    'milex.lead.field.schema_definition',
                    'monolog.logger.milex',
                    'milex.lead.field.lead_field_saver',
                    'milex.lead.field.custom_field_index',
                    'milex.lead.field.dispatcher.field_column_dispatcher',
                    'translator',
                ],
            ],
            'milex.lead.field.custom_field_index' => [
                'class'     => Milex\LeadBundle\Field\CustomFieldIndex::class,
                'arguments' => [
                    'milex.schema.helper.index',
                    'monolog.logger.milex',
                    'milex.lead.field.fields_with_unique_identifier',
                ],
            ],
            'milex.lead.field.dispatcher.field_save_dispatcher' => [
                'class'     => Milex\LeadBundle\Field\Dispatcher\FieldSaveDispatcher::class,
                'arguments' => [
                    'event_dispatcher',
                    'doctrine.orm.entity_manager',
                ],
            ],
            'milex.lead.field.dispatcher.field_column_dispatcher' => [
                'class'     => Milex\LeadBundle\Field\Dispatcher\FieldColumnDispatcher::class,
                'arguments' => [
                    'event_dispatcher',
                    'milex.lead.field.settings.background_settings',
                ],
            ],
            'milex.lead.field.dispatcher.field_column_background_dispatcher' => [
                'class'     => Milex\LeadBundle\Field\Dispatcher\FieldColumnBackgroundJobDispatcher::class,
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
            'milex.lead.field.fields_with_unique_identifier' => [
                'class'     => Milex\LeadBundle\Field\FieldsWithUniqueIdentifier::class,
                'arguments' => [
                    'milex.lead.field.field_list',
                ],
            ],
            'milex.lead.field.field_list' => [
                'class'     => Milex\LeadBundle\Field\FieldList::class,
                'arguments' => [
                    'milex.lead.repository.field',
                    'translator',
                ],
            ],
            'milex.lead.field.identifier_fields' => [
                'class'     => \Milex\LeadBundle\Field\IdentifierFields::class,
                'arguments' => [
                    'milex.lead.field.fields_with_unique_identifier',
                    'milex.lead.field.field_list',
                ],
            ],
            'milex.lead.field.lead_field_saver' => [
                'class'     => Milex\LeadBundle\Field\LeadFieldSaver::class,
                'arguments' => [
                    'milex.lead.repository.field',
                    'milex.lead.field.dispatcher.field_save_dispatcher',
                ],
            ],
            'milex.lead.field.settings.background_settings' => [
                'class'     => Milex\LeadBundle\Field\Settings\BackgroundSettings::class,
                'arguments' => [
                    'milex.helper.core_parameters',
                ],
            ],
            'milex.lead.field.settings.background_service' => [
                'class'     => Milex\LeadBundle\Field\BackgroundService::class,
                'arguments' => [
                    'milex.lead.model.field',
                    'milex.lead.field.custom_field_column',
                    'milex.lead.field.lead_field_saver',
                    'milex.lead.field.dispatcher.field_column_background_dispatcher',
                    'milex.lead.field.notification.custom_field',
                ],
            ],
            'milex.lead.field.notification.custom_field' => [
                'class'     => Milex\LeadBundle\Field\Notification\CustomFieldNotification::class,
                'arguments' => [
                    'milex.core.model.notification',
                    'milex.user.model.user',
                    'translator',
                ],
            ],
        ],
        'command' => [
            'milex.lead.command.deduplicate' => [
                'class'     => \Milex\LeadBundle\Command\DeduplicateCommand::class,
                'arguments' => [
                    'milex.lead.deduper',
                    'translator',
                ],
                'tag' => 'console.command',
            ],
            'milex.lead.command.create_custom_field' => [
                'class'     => \Milex\LeadBundle\Field\Command\CreateCustomFieldCommand::class,
                'arguments' => [
                    'milex.lead.field.settings.background_service',
                    'translator',
                    'milex.lead.repository.field',
                ],
                'tag' => 'console.command',
            ],
        ],
        'fixtures' => [
            'milex.lead.fixture.company' => [
                'class'     => \Milex\LeadBundle\DataFixtures\ORM\LoadCompanyData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.lead.model.company'],
            ],
            'milex.lead.fixture.contact' => [
                'class'     => \Milex\LeadBundle\DataFixtures\ORM\LoadLeadData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['doctrine.orm.entity_manager', 'milex.helper.core_parameters'],
            ],
            'milex.lead.fixture.contact_field' => [
                'class'     => \Milex\LeadBundle\DataFixtures\ORM\LoadLeadFieldData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => [],
            ],
            'milex.lead.fixture.segment' => [
                'class'     => \Milex\LeadBundle\DataFixtures\ORM\LoadLeadListData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.lead.model.list'],
            ],
            'milex.lead.fixture.category' => [
                'class'     => \Milex\LeadBundle\DataFixtures\ORM\LoadCategoryData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['doctrine.orm.entity_manager'],
            ],
            'milex.lead.fixture.categorizedleadlists' => [
                'class'     => \Milex\LeadBundle\DataFixtures\ORM\LoadCategorizedLeadListData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['doctrine.orm.entity_manager'],
            ],
            'milex.lead.fixture.test.page_hit' => [
                'class'     => \Milex\LeadBundle\Tests\DataFixtures\ORM\LoadPageHitData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'optional'  => true,
            ],
            'milex.lead.fixture.test.segment' => [
                'class'     => \Milex\LeadBundle\Tests\DataFixtures\ORM\LoadSegmentsData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.lead.model.list', 'milex.lead.model.lead'],
                'optional'  => true,
            ],
            'milex.lead.fixture.test.click' => [
                'class'     => \Milex\LeadBundle\Tests\DataFixtures\ORM\LoadClickData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.lead.model.list', 'milex.lead.model.lead'],
                'optional'  => true,
            ],
            'milex.lead.fixture.test.dnc' => [
                'class'     => \Milex\LeadBundle\Tests\DataFixtures\ORM\LoadDncData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'arguments' => ['milex.lead.model.list', 'milex.lead.model.lead'],
                'optional'  => true,
            ],
            'milex.lead.fixture.test.tag' => [
                'class'     => \Milex\LeadBundle\Tests\DataFixtures\ORM\LoadTagData::class,
                'tag'       => \Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG,
                'optional'  => true,
            ],
        ],
    ],
    'parameters' => [
        'parallel_import_limit'               => 1,
        'background_import_if_more_rows_than' => 0,
        'contact_columns'                     => [
            '0' => 'name',
            '1' => 'email',
            '2' => 'location',
            '3' => 'stage',
            '4' => 'points',
            '5' => 'last_active',
            '6' => 'id',
        ],
        \Milex\LeadBundle\Field\Settings\BackgroundSettings::CREATE_CUSTOM_FIELD_IN_BACKGROUND => false,
        'company_unique_identifiers_operator'                                                   => \Doctrine\DBAL\Query\Expression\CompositeExpression::TYPE_OR,
        'contact_unique_identifiers_operator'                                                   => \Doctrine\DBAL\Query\Expression\CompositeExpression::TYPE_OR,
        'segment_rebuild_time_warning'                                                          => 30,
    ],
];
