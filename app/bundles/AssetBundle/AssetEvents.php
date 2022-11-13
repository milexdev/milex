<?php

namespace Milex\AssetBundle;

/**
 * Events available for AssetBundle.
 */
final class AssetEvents
{
    /**
     * The milex.asset_on_load event is dispatched when a public asset is downloaded, publicly viewed, or redirected to (remote).
     *
     * The event listener receives a
     * Milex\AssetBundle\Event\AssetLoadEvent instance.
     *
     * @var string
     */
    const ASSET_ON_LOAD = 'milex.asset_on_load';

    /**
     * The milex.asset_on_remote_browse event is dispatched when browsing a remote provider.
     *
     * The event listener receives a
     * Milex\AssetBundle\Event\RemoteAssetBrowseEvent instance.
     *
     * @var string
     */
    const ASSET_ON_REMOTE_BROWSE = 'milex.asset_on_remote_browse';

    /**
     * The milex.asset_on_upload event is dispatched before uploading a file.
     *
     * The event listener receives a
     * Milex\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    const ASSET_ON_UPLOAD = 'milex.asset_on_upload';

    /**
     * The milex.asset_pre_save event is dispatched right before a asset is persisted.
     *
     * The event listener receives a
     * Milex\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    const ASSET_PRE_SAVE = 'milex.asset_pre_save';

    /**
     * The milex.asset_post_save event is dispatched right after a asset is persisted.
     *
     * The event listener receives a
     * Milex\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    const ASSET_POST_SAVE = 'milex.asset_post_save';

    /**
     * The milex.asset_pre_delete event is dispatched prior to when a asset is deleted.
     *
     * The event listener receives a
     * Milex\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    const ASSET_PRE_DELETE = 'milex.asset_pre_delete';

    /**
     * The milex.asset_post_delete event is dispatched after a asset is deleted.
     *
     * The event listener receives a
     * Milex\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    const ASSET_POST_DELETE = 'milex.asset_post_delete';

    /**
     * The milex.asset.on_campaign_trigger_decision event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Milex\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_DECISION = 'milex.asset.on_campaign_trigger_decision';

    /**
     * The milex.asset.on_download_rate_winner event is fired when there is a need to determine download rate winner.
     *
     * The event listener receives a
     * Milex\CoreBundles\Event\DetermineWinnerEvent
     *
     * @var string
     */
    const ON_DETERMINE_DOWNLOAD_RATE_WINNER = 'milex.asset.on_download_rate_winner';
}
