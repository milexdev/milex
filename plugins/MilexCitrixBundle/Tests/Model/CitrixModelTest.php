<?php

namespace MilexPlugin\MilexCitrixBundle\Tests\Model;

use Milex\CoreBundle\Test\MilexMysqlTestCase;
use MilexPlugin\MilexCitrixBundle\Model\CitrixModel;
use MilexPlugin\MilexCitrixBundle\Tests\DataFixtures\ORM\LoadCitrixData;

class CitrixModelTest extends MilexMysqlTestCase
{
    public function testCountEventsBy()
    {
        $this->loadFixtures([LoadCitrixData::class]);

        /** @var CitrixModel $model */
        $model = self::$container->get('milex.citrix.model.citrix');
        $count = $model->countEventsBy('webinar', "joe.o'connor@domain.com", 'registered', ['sample-webinar_#0000']);
        $this->assertEquals(1, $count);
    }
}
