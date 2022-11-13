<?php

namespace Milex\WebhookBundle\Tests\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Milex\CoreBundle\Entity\IpAddress;
use Milex\LeadBundle\Entity\Company;
use Milex\LeadBundle\Entity\CompanyRepository;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\CompanyModel;
use Milex\WebhookBundle\Helper\CampaignHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CampaignHelperTest extends \PHPUnit\Framework\TestCase
{
    private $contact;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Client
     */
    private $client;

    private $companyModel;
    private $companyRepository;

    /**
     * @var ArrayCollection
     */
    private $ipCollection;

    /**
     * @var CampaignHelper
     */
    private $campaignHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contact           = $this->createMock(Lead::class);
        $this->client            = $this->createMock(Client::class);
        $this->companyModel      = $this->createMock(CompanyModel::class);
        $this->dispatcher        = $this->createMock(EventDispatcherInterface::class);
        $this->ipCollection      = new ArrayCollection();
        $this->companyRepository = $this->getMockBuilder(CompanyRepository::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['getCompaniesByLeadId'])
        ->getMock();

        $this->companyRepository->method('getCompaniesByLeadId')
        ->willReturn([new Company()]);

        $this->companyModel->method('getRepository')
        ->willReturn($this->companyRepository);

        $this->campaignHelper = new CampaignHelper($this->client, $this->companyModel, $this->dispatcher);

        $this->ipCollection->add((new IpAddress())->setIpAddress('127.0.0.1'));
        $this->ipCollection->add((new IpAddress())->setIpAddress('127.0.0.2'));

        $this->contact->expects($this->once())
            ->method('getProfileFields')
            ->willReturn(['email' => 'john@doe.email', 'company' => 'Milex']);

        $this->contact->expects($this->once())
            ->method('getIpAddresses')
            ->willReturn($this->ipCollection);
    }

    public function testFireWebhookWithGet()
    {
        $expectedUrl = 'https://milex.org?test=tee&email=john%40doe.email&IP=127.0.0.1%2C127.0.0.2';

        $this->client->expects($this->once())
            ->method('get')
            ->with($expectedUrl, [
                \GuzzleHttp\RequestOptions::HEADERS => ['test' => 'tee', 'company' => 'Milex'],
                \GuzzleHttp\RequestOptions::TIMEOUT => 10,
            ])
            ->willReturn(new Response(200));

        $this->campaignHelper->fireWebhook($this->provideSampleConfig(), $this->contact);
    }

    public function testFireWebhookWithPost()
    {
        $config      = $this->provideSampleConfig('post');
        $expectedUrl = 'https://milex.org?test=tee&email=john%40doe.email&IP=127.0.0.1%2C127.0.0.2';

        $this->client->expects($this->once())
            ->method('request')
            ->with('post', 'https://milex.org', [
                \GuzzleHttp\RequestOptions::FORM_PARAMS => ['test'  => 'tee', 'email' => 'john@doe.email', 'IP' => '127.0.0.1,127.0.0.2'],
                \GuzzleHttp\RequestOptions::HEADERS     => ['test' => 'tee', 'company' => 'Milex'],
                \GuzzleHttp\RequestOptions::TIMEOUT     => 10,
            ])
            ->willReturn(new Response(200));

        $this->campaignHelper->fireWebhook($config, $this->contact);
    }

    public function testFireWebhookWithPostJson()
    {
        $config      = $this->provideSampleConfig('post');
        $expectedUrl = 'https://milex.org?test=tee&email=john%40doe.email&IP=127.0.0.1%2C127.0.0.2';

        $config      = $this->provideSampleConfig('post', 'application/json');
        $this->client->expects($this->once())
            ->method('request')
            ->with('post', 'https://milex.org', [
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'test'         => 'tee',
                    'company'      => 'Milex',
                    'content-type' => 'application/json',
                ],
                \GuzzleHttp\RequestOptions::TIMEOUT => 10,
                \GuzzleHttp\RequestOptions::BODY    => json_encode(
                    ['test' => 'tee', 'email' => 'john@doe.email', 'IP' => '127.0.0.1,127.0.0.2']
                ),
            ])
            ->willReturn(new Response(200));

        $this->campaignHelper->fireWebhook($config, $this->contact);
    }

    public function testFireWebhookWhenReturningNotFound()
    {
        $this->client->expects($this->once())
            ->method('get')
            ->willReturn(new Response(404));

        $this->expectException(\OutOfRangeException::class);

        $this->campaignHelper->fireWebhook($this->provideSampleConfig(), $this->contact);
    }

    private function provideSampleConfig($method = 'get', $type = 'application/x-www-form-urlencoded')
    {
        $sample = [
            'url'             => 'https://milex.org',
            'method'          => $method,
            'timeout'         => 10,
            'additional_data' => [
                'list' => [
                    [
                        'label' => 'test',
                        'value' => 'tee',
                    ],
                    [
                        'label' => 'email',
                        'value' => '{contactfield=email}',
                    ],
                    [
                        'label' => 'IP',
                        'value' => '{contactfield=ipAddress}',
                    ],
                ],
            ],
            'headers' => [
                'list' => [
                    [
                        'label' => 'test',
                        'value' => 'tee',
                    ],
                    [
                        'label' => 'company',
                        'value' => '{contactfield=company}',
                    ],
                ],
            ],
        ];
        if ('application/json' == $type) {
            array_push($sample['headers']['list'],
            [
                'label' => 'content-type',
                'value' => 'application/json',
            ]);
        }

        return $sample;
    }
}
