<?php

use Fusio\Adapter\Php\Action\PhpExecutorAbstract;
use Fusio\Adapter\Php\Action\PhpProcessor;
use Fusio\Adapter\Php\Action\PhpSandbox;
use Fusio\Engine\Adapter\ServiceBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = ServiceBuilder::build($container);
    $services->set(PhpProcessor::class);
    $services->set(PhpSandbox::class);
};
