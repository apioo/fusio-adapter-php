<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Adapter\Php\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\ResponseInterface;

/**
 * PhpProcessor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class PhpProcessor extends ActionAbstract
{
    public function getName()
    {
        return 'PHP-Processor';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $resp = runScript($configuration->get('file'), [
            'request' => $request,
            'context' => $context,
            'connector' => $this->connector,
            'response' => $this->response,
            'processor' => $this->processor,
            'logger' => $this->logger,
            'cache' => $this->cache,
        ]);

        if ($resp instanceof ResponseInterface) {
            return $resp;
        } else {
            return $this->response->build(204, [], []);
        }
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newInput('file', 'File', 'text', 'Click <a ng-click="help.showDialog(\'help/action/php.md\')">here</a> for more information.'));
    }
}

function runScript($file, array $ctx)
{
    extract($ctx);
    return require $file;
}
