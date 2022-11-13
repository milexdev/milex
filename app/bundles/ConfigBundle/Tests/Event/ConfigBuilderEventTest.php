<?php

namespace Milex\ConfigBundle\Tests\Event;

use Milex\ConfigBundle\Event\ConfigBuilderEvent;
use Milex\CoreBundle\Tests\CommonMocks;

class ConfigBuilderEventTest extends CommonMocks
{
    public function testAddForm()
    {
        $event  = $this->initEvent();
        $form   = ['formAlias' => 'testform'];
        $result = $event->addForm($form);

        $this->assertTrue($result instanceof ConfigBuilderEvent);

        $forms = $event->getForms();

        $this->assertEquals($form, $forms[$form['formAlias']]);
    }

    public function testRemoveForm()
    {
        $event = $this->initEvent();
        $form  = ['formAlias' => 'testform'];

        $event->addForm($form);

        $result = $event->removeForm($form['formAlias']);
        $forms  = $event->getForms();

        $this->assertEquals([], $forms);
        $this->assertTrue($result);
    }

    protected function initEvent()
    {
        return new ConfigBuilderEvent($this->getBundleHelperMock());
    }
}
