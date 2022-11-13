<?php

namespace Milex\CoreBundle\Test;

trait IsolatedTestTrait
{
    /**
     * Ensure the MILEX_TABLE_PREFIX const is correctly set in isolated tests.
     *
     * Those test runs don't get the constant set in MilexExtension::executeBeforeFirstTest(), so we need to redefine it.
     */
    public static function setUpBeforeClass(): void
    {
        if (!defined('MILEX_TABLE_PREFIX')) {
            EnvLoader::load();
            $prefix = false === getenv('MILEX_DB_PREFIX') ? 'test_' : getenv('MILEX_DB_PREFIX');
            define('MILEX_TABLE_PREFIX', $prefix);
        }
    }
}
