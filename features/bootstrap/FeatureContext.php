<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Blumilk\BLT\Bootstrapping\LaravelBootstrapper;
use Blumilk\BLT\Features\Toolbox;

class FeatureContext extends Toolbox implements Context
{
    public function __construct()
    {
        $bootstrapper = new LaravelBootstrapper();
        $bootstrapper->boot();
    }
}
