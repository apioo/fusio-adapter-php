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
use Fusio\Adapter\Php\Action\PhpProcessor;
use Fusio\Engine\Form\Builder;
use Fusio\Engine\Form\Container;
use Fusio\Engine\Form\Element\Input;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Record\Record;

/**
 * PhpProcessorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class PhpProcessorTest extends PhpTestCase
{
    public function testHandle()
    {
        $action = $this->getActionFactory()->factory(PhpProcessor::class);

        // handle request
        $response = $action->handle(
            $this->getRequest(
                'GET', 
                ['foo' => 'bar'], 
                ['foo' => 'bar'], 
                ['Content-Type' => 'application/json'], 
                Record::fromArray(['foo' => 'bar'])
            ),
            $this->getParameters(['file' => __DIR__ . '/script.php']),
            $this->getContext()
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

    public function testGetForm()
    {
        $action  = $this->getActionFactory()->factory(PhpProcessor::class);
        $builder = new Builder();
        $factory = $this->getFormElementFactory();

        $action->configure($builder, $factory);

        $this->assertInstanceOf(Container::class, $builder->getForm());

        $elements = $builder->getForm()->getElements();
        $this->assertEquals(1, count($elements));
        $this->assertInstanceOf(Input::class, $elements[0]);
    }
}
