<?php

namespace Milex\EmailBundle\Tests\MonitoredEmail\Processor\Bounce\Mapper;

use Milex\EmailBundle\MonitoredEmail\Exception\CategoryNotFound;
use Milex\EmailBundle\MonitoredEmail\Processor\Bounce\Definition\Category as Definition;
use Milex\EmailBundle\MonitoredEmail\Processor\Bounce\Mapper\Category;
use Milex\EmailBundle\MonitoredEmail\Processor\Bounce\Mapper\CategoryMapper;

class CategoryMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox Test that the Category object is returned
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Bounce\Mapper\CategoryMapper::map()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Bounce\Definition\Category
     */
    public function testCategoryIsMapped()
    {
        $category = CategoryMapper::map(Definition::ANTISPAM);

        $this->assertInstanceOf(Category::class, $category);
    }

    /**
     * @testdox Test that exception is thrown if a category is not found
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Bounce\Mapper\CategoryMapper::map()
     */
    public function testExceptionIsThrownWithUnrecognizedCategory()
    {
        $this->expectException(CategoryNotFound::class);

        CategoryMapper::map('bippitybop');
    }
}
