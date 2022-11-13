<?php

namespace Milex\QueueBundle;

/**
 * Class MilexQueueEvents
 * Events available for MilexQueueBundle.
 */
final class QueueEvents
{
    const CONSUME_MESSAGE = 'milex.queue_consume_message';

    const PUBLISH_MESSAGE = 'milex.queue_publish_message';

    const EMAIL_HIT = 'milex.queue_email_hit';

    const PAGE_HIT = 'milex.queue_page_hit';

    const TRANSPORT_WEBHOOK = 'milex.queue_transport_webhook';
}
