<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Adapter\Php\Tests\Action;

use Fusio\Adapter\Php\Tests\PhpTestCase;
use Fusio\Adapter\Php\Action\PhpSandbox;
use Fusio\Engine\Form\Builder;
use Fusio\Engine\Form\Container;
use Fusio\Engine\Form\Element\TextArea;
use Fusio\Engine\Model\Action;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Record\Record;
use PSX\Sandbox\SecurityException;

/**
 * PhpSandboxTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class PhpSandboxTest extends PhpTestCase
{
    public function testHandle()
    {
        $action = $this->getActionFactory()->factory(PhpSandbox::class);

        $code = <<<'PHP'
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

if (!$request instanceof \Fusio\Engine\RequestInterface) {
    throw new \RuntimeException('Error');
}

if (!$context instanceof \Fusio\Engine\ContextInterface) {
    throw new \RuntimeException('Error');
}

if (!$connector instanceof \Fusio\Engine\ConnectorInterface) {
    throw new \RuntimeException('Error');
}

if (!$response instanceof \Fusio\Engine\Response\FactoryInterface) {
    throw new \RuntimeException('Error');
}

if (!$processor instanceof \Fusio\Engine\ProcessorInterface) {
    throw new \RuntimeException('Error');
}

if (!$dispatcher instanceof \Fusio\Engine\DispatcherInterface) {
    throw new \RuntimeException('Error');
}

if (!$logger instanceof \Psr\Log\LoggerInterface) {
    throw new \RuntimeException('Error');
}

if (!$cache instanceof \Psr\SimpleCache\CacheInterface) {
    throw new \RuntimeException('Error');
}

return $response->build(200, ['X-Foo' => 'bar'], [
    'foo' => 'bar'
]);

PHP;

        $actionModel = new Action(1, 'test-action', '', '', false, []);

        $context    = $this->getContext()->withAction($actionModel);
        $parameters = $this->getParameters(['code' => $code]);

        // call create to setup php script
        $action->onCreate($actionModel->getName(), $parameters);

        // handle request
        $response = $action->handle(
            $this->getRequest(
                'GET', 
                ['foo' => 'bar'], 
                ['foo' => 'bar'], 
                ['Content-Type' => 'application/json'], 
                Record::fromArray(['foo' => 'bar'])
            ),
            $parameters,
            $context
        );

        $actual = json_encode($response->getBody(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "foo": "bar"
}
JSON;

        $this->assertInstanceOf(HttpResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['x-foo' => 'bar'], $response->getHeaders());
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testHandleInvalidCode()
    {
        $this->expectException(SecurityException::class);

        $action = $this->getActionFactory()->factory(PhpSandbox::class);

        $code = <<<'PHP'
<?php

$return = shell_exec('ls -l');

return $response->build(200, ['X-Foo' => 'bar'], [
    'foo' => $return
]);

PHP;

        $parameters = $this->getParameters(['code' => $code]);

        $action->onCreate('invalid-action', $parameters);
    }

    public function testLifecycle()
    {
        $action = $this->getActionFactory()->factory(PhpSandbox::class);

        $codeCreate = '<?php' . "\n\n" . 'return $response->build(200, [], ["foo" => "bar"]);';
        $codeUpdate = '<?php' . "\n\n" . 'return $response->build(201, [], ["foo" => "baz"]);';

        $parameters = $this->getParameters(['code' => $codeCreate]);
        $name = 'test-lifecycle-action';
        $file = $this->getActionFile($name);

        $action->onCreate($name, $parameters);
        
        $this->assertFileExists($file);
        $this->assertEquals($codeCreate, file_get_contents($file));

        $parameters->set('code', $codeUpdate);

        $action->onUpdate($name, $parameters);

        $this->assertFileExists($file);
        $this->assertEquals($codeUpdate, file_get_contents($file));

        $action->onDelete($name, $parameters);

        $this->assertFileNotExists($file);
    }

    public function testGetForm()
    {
        $action  = $this->getActionFactory()->factory(PhpSandbox::class);
        $builder = new Builder();
        $factory = $this->getFormElementFactory();

        $action->configure($builder, $factory);

        $this->assertInstanceOf(Container::class, $builder->getForm());

        $elements = $builder->getForm()->getElements();
        $this->assertEquals(1, count($elements));
        $this->assertInstanceOf(TextArea::class, $elements[0]);
    }

    private function getActionFile(string $name): string
    {
        if (defined('PSX_PATH_CACHE')) {
            $basePath = PSX_PATH_CACHE;
        } else {
            $basePath = sys_get_temp_dir();
        }

        return $basePath . '/sandbox_' . substr(md5($name), 0, 8) . '.php';
    }
}
