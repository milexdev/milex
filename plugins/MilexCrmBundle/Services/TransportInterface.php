<?php

namespace MilexPlugin\MilexCrmBundle\Services;

interface TransportInterface
{
    public function post($uri, array $options);

    public function put($uri, array $options);

    public function get($uri, array $options);

    public function delete($uri, array $options);
}
