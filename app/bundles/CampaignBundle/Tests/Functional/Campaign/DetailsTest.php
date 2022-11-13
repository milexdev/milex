<?php

declare(strict_types=1);

namespace Milex\CampaignBundle\Tests\Functional\Campaign;

use Milex\CampaignBundle\Entity\Campaign;
use Milex\CoreBundle\Test\MilexMysqlTestCase;
use PHPUnit\Framework\Assert;

class DetailsTest extends MilexMysqlTestCase
{
    public function testDetailsPageLoadCorrectly(): void
    {
        $campaign = new Campaign();
        $campaign->setName('Campaign A');
        $campaign->setCanvasSettings([
                'nodes' => [
                    0 => [
                        'id'        => '148',
                        'positionX' => '760',
                        'positionY' => '155',
                    ],
                    1 => [
                        'id'        => 'lists',
                        'positionX' => '860',
                        'positionY' => '50',
                    ],
                ],
                'connections' => [
                    0 => [
                        'sourceId' => 'lists',
                        'targetId' => '148',
                        'anchors'  => [
                            'source' => 'leadsource',
                            'target' => 'top',
                        ],
                    ],
                ],
            ]
        );
        $this->em->persist($campaign);
        $this->em->flush();

        $this->client->request('GET', sprintf('/s/campaigns/view/%s', $campaign->getId()));

        $response = $this->client->getResponse();
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertStringContainsString($campaign->getName(), $response->getContent());
    }
}
