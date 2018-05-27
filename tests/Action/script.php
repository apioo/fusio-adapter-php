<?php
/**
 * @var $request \Fusio\Engine\RequestInterface
 * @var $context \Fusio\Engine\ContextInterface
 * @var $connector \Fusio\Engine\ConnectorInterface
 * @var $response \Fusio\Engine\Response\FactoryInterface
 * @var $processor \Fusio\Engine\ProcessorInterface
 * @var $dispatcher \Fusio\Engine\DispatcherInterface
 * @var $logger \Psr\Log\LoggerInterface
 * @var $cache \Psr\SimpleCache\CacheInterface
 */

assert($request instanceof \Fusio\Engine\RequestInterface);
assert($context instanceof \Fusio\Engine\ContextInterface);
assert($connector instanceof \Fusio\Engine\ConnectorInterface);
assert($response instanceof \Fusio\Engine\Response\FactoryInterface);
assert($processor instanceof \Fusio\Engine\ProcessorInterface);
assert($dispatcher instanceof \Fusio\Engine\DispatcherInterface);
assert($logger instanceof \Psr\Log\LoggerInterface);
assert($cache instanceof \Psr\SimpleCache\CacheInterface);

return $response->build(200, ['X-Foo' => 'bar'], [
    'foo' => 'bar'
]);
