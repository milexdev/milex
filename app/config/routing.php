<?php

use Symfony\Component\Routing\RouteCollection;

//loads Milex's custom routing in src/Milex/BaseBundle/Routing/MilexLoader.php which
//loads all of the Milex bundles' routing.php files
$collection = new RouteCollection();
$collection->addCollection($loader->import('.', 'milex'));

return $collection;
