<?php

namespace Milex\CoreBundle;

/**
 * Class CoreEvents.
 */
final class CoreEvents
{
    /**
     * The milex.build_menu event is thrown to render menu items.
     *
     * The event listener receives a Milex\CoreBundle\Event\MenuEvent instance.
     *
     * @var string
     */
    const BUILD_MENU = 'milex.build_menu';

    /**
     * The milex.build_route event is thrown to build Milex bundle routes.
     *
     * The event listener receives a Milex\CoreBundle\Event\RouteEvent instance.
     *
     * @var string
     */
    const BUILD_ROUTE = 'milex.build_route';

    /**
     * The milex.global_search event is thrown to build global search results from applicable bundles.
     *
     * The event listener receives a Milex\CoreBundle\Event\GlobalSearchEvent instance.
     *
     * @var string
     */
    const GLOBAL_SEARCH = 'milex.global_search';

    /**
     * The milex.list_stats event is thrown to build statistical results from applicable bundles/database tables.
     *
     * The event listener receives a Milex\CoreBundle\Event\StatsEvent instance.
     *
     * @var string
     */
    const LIST_STATS = 'milex.list_stats';

    /**
     * The milex.build_command_list event is thrown to build global search's autocomplete list.
     *
     * The event listener receives a Milex\CoreBundle\Event\CommandListEvent instance.
     *
     * @var string
     */
    const BUILD_COMMAND_LIST = 'milex.build_command_list';

    /**
     * The milex.on_fetch_icons event is thrown to fetch icons of menu items.
     *
     * The event listener receives a Milex\CoreBundle\Event\IconEvent instance.
     *
     * @var string
     */
    const FETCH_ICONS = 'milex.on_fetch_icons';

    /**
     * The milex.build_canvas_content event is dispatched to populate the content for the right panel.
     *
     * The event listener receives a Milex\CoreBundle\Event\SidebarCanvasEvent instance.
     *
     * @var string
     *
     * @deprecated Deprecated in Milex 4.3. Will be removed in Milex 5.0
     */
    const BUILD_CANVAS_CONTENT = 'milex.build_canvas_content';

    /**
     * The milex.pre_upgrade is dispatched before an upgrade.
     *
     * The event listener receives a Milex\CoreBundle\Event\UpgradeEvent instance.
     *
     * @var string
     */
    const PRE_UPGRADE = 'milex.pre_upgrade';

    /**
     * The milex.post_upgrade is dispatched after an upgrade.
     *
     * The event listener receives a Milex\CoreBundle\Event\UpgradeEvent instance.
     *
     * @var string
     */
    const POST_UPGRADE = 'milex.post_upgrade';

    /**
     * The milex.build_embeddable_js event is dispatched to allow plugins to extend the milex tracking js.
     *
     * The event listener receives a Milex\CoreBundle\Event\BuildJsEvent instance.
     *
     * @var string
     */
    const BUILD_MILEX_JS = 'milex.build_embeddable_js';

    /**
     * The milex.maintenance_cleanup_data event is dispatched to purge old data.
     *
     * The event listener receives a Milex\CoreBundle\Event\MaintenanceEvent instance.
     *
     * @var string
     */
    const MAINTENANCE_CLEANUP_DATA = 'milex.maintenance_cleanup_data';

    /**
     * The milex.view_inject_custom_buttons event is dispatched to inject custom buttons into Milex's UI by plugins/other bundles.
     *
     * The event listener receives a Milex\CoreBundle\Event\CustomButtonEvent instance.
     *
     * @var string
     */
    const VIEW_INJECT_CUSTOM_BUTTONS = 'milex.view_inject_custom_buttons';

    /**
     * The milex.view_inject_custom_content event is dispatched by views to collect custom content to be injected in UIs.
     *
     * The event listener receives a Milex\CoreBundle\Event\CustomContentEvent instance.
     *
     * @var string
     */
    const VIEW_INJECT_CUSTOM_CONTENT = 'milex.view_inject_custom_content';

    /**
     * The milex.view_inject_custom_template event is dispatched when a template is to be rendered giving opportunity to change template or
     * vars.
     *
     * The event listener receives a Milex\CoreBundle\Event\CustomTemplateEvent instance.
     *
     * @var string
     */
    const VIEW_INJECT_CUSTOM_TEMPLATE = 'milex.view_inject_custom_template';

    /**
     * The milex.view_inject_custom_assets event is dispatched when assets are rendered.
     *
     * The event listener receives a Milex\CoreBundle\Event\CustomAssetsEvent instance.
     *
     * @var string
     */
    const VIEW_INJECT_CUSTOM_ASSETS = 'milex.view_inject_custom_assets';

    /**
     * The milex.on_form_type_build event is dispatched by views to inject custom fields into any form.
     *
     * The event listener receives a Milex\CoreBundle\Event\CustomFormEvent instance.
     *
     * @var string
     *
     * @deprecated since Milex 4 because it is not used anywhere
     */
    const ON_FORM_TYPE_BUILD = 'milex.on_form_type_build';

    /**
     * The milex.on_generated_columns_build event is dispatched when a list of generated columns is being built.
     *
     * The event listener receives a Milex\CoreBundle\Event\GeneratedColumnsEvent instance.
     *
     * @var string
     */
    const ON_GENERATED_COLUMNS_BUILD = 'milex.on_generated_columns_build';
}
