<?php

namespace Aesis\ApiConcern\Tests;

use Aesis\ApiConcern\ApiConcernServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [ApiConcernServiceProvider::class];
    }
}
