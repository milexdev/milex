<?php

namespace Milex\CoreBundle\IpLookup\DoNotSellList;

interface DoNotSellListInterface extends \Iterator
{
    public function loadList(): bool;
}
