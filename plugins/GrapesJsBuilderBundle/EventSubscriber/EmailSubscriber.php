<?php

declare(strict_types=1);

namespace MilexPlugin\GrapesJsBuilderBundle\EventSubscriber;

use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Event as Events;
use MilexPlugin\GrapesJsBuilderBundle\Integration\Config;
use MilexPlugin\GrapesJsBuilderBundle\Model\GrapesJsBuilderModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailSubscriber implements EventSubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var GrapesJsBuilderModel
     */
    private $grapesJsBuilderModel;

    /**
     * EmailSubscriber constructor.
     */
    public function __construct(Config $config, GrapesJsBuilderModel $grapesJsBuilderModel)
    {
        $this->config               = $config;
        $this->grapesJsBuilderModel = $grapesJsBuilderModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_POST_SAVE   => ['onEmailPostSave', 0],
            EmailEvents::EMAIL_POST_DELETE => ['onEmailDelete', 0],
        ];
    }

    /**
     * Add an entry.
     */
    public function onEmailPostSave(Events\EmailEvent $event)
    {
        if (!$this->config->isPublished()) {
            return;
        }

        $this->grapesJsBuilderModel->addOrEditEntity($event->getEmail());
    }

    /**
     * Delete an entry.
     */
    public function onEmailDelete(Events\EmailEvent $event)
    {
        if (!$this->config->isPublished()) {
            return;
        }

        $email           = $event->getEmail();
        $grapesJsBuilder = $this->grapesJsBuilderModel->getRepository()->findOneBy(['email' => $email]);

        if ($grapesJsBuilder) {
            $this->grapesJsBuilderModel->getRepository()->deleteEntity($grapesJsBuilder);
        }
    }
}
