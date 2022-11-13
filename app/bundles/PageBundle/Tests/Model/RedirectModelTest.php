<?php

namespace Milex\PageBundle\Tests\Model;

use Milex\CoreBundle\Helper\UrlHelper;
use Milex\PageBundle\Entity\Redirect;
use Milex\PageBundle\Event\RedirectGenerationEvent;
use Milex\PageBundle\Model\RedirectModel;
use Milex\PageBundle\PageEvents;
use Milex\PageBundle\Tests\PageTestAbstract;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RedirectModelTest extends PageTestAbstract
{
    public function testCreateRedirectEntityWhenCalledReturnsRedirect()
    {
        $redirectModel = $this->getRedirectModel();
        $entity        = $redirectModel->createRedirectEntity('http://some-url.com');

        $this->assertInstanceOf(Redirect::class, $entity);
    }

    public function testGenerateRedirectUrlWhenCalledReturnsValidUrl()
    {
        $redirect = new Redirect();
        $redirect->setUrl('http://some-url.com');
        $redirect->setRedirectId('redirect-id');

        $redirectModel = $this->getRedirectModel();
        $url           = $redirectModel->generateRedirectUrl($redirect);

        $this->assertStringContainsString($url, 'http://some-url.com');
    }

    public function testRedirectGenerationEvent()
    {
        $urlHelper = $this
            ->getMockBuilder(UrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model = new RedirectModel($urlHelper);

        $dispatcher = new EventDispatcher();
        $model->setDispatcher($dispatcher);

        $url          = 'https://milex.org';
        $clickthrough = ['foo' => 'bar'];

        $router = $this->createMock(Router::class);
        $router->expects($this->exactly(2))
            ->method('generate')
            ->willReturn($url);
        $model->setRouter($router);

        $redirect = new Redirect();
        $redirect->setUrl($url);

        // URL should just have foo = bar in the CT
        $url = $model->generateRedirectUrl($redirect, $clickthrough);
        $this->assertEquals('https://milex.org?ct=YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30%3D', $url);

        // Add the listener to append something else to the CT
        $dispatcher->addListener(
            PageEvents::ON_REDIRECT_GENERATE,
            function (RedirectGenerationEvent $event) {
                $event->setInClickthrough('bar', 'foo');
            }
        );
        $url = $model->generateRedirectUrl($redirect, $clickthrough);
        $this->assertEquals('https://milex.org?ct=YToyOntzOjM6ImZvbyI7czozOiJiYXIiO3M6MzoiYmFyIjtzOjM6ImZvbyI7fQ%3D%3D', $url);
    }
}
