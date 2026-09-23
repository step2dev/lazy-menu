<?php

namespace Step2dev\LazyMenu\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Step2dev\LazyMenu\LazyMenuServiceProvider;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }

    protected function getPackageProviders($app)
    {
        return [LazyMenuServiceProvider::class];
    }
}
