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

namespace Fusio\Adapter\Php\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\RequestInterface;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * PhpExecutorAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
abstract class PhpExecutorAbstract extends ActionAbstract
{
    public function execute(string $file, RequestInterface $request, ContextInterface $context): HttpResponseInterface
    {
        $response = runScript($file, [
            'request' => $request,
            'context' => $context,
            'connector' => $this->connector,
            'response' => $this->response,
            'processor' => $this->processor,
            'dispatcher' => $this->dispatcher,
            'logger' => $this->logger,
            'cache' => $this->cache,
        ]);

        if ($response instanceof HttpResponseInterface) {
            return $response;
        } else {
            return $this->response->build(204, [], []);
        }
    }
}

function runScript(string $file, array $ctx): mixed
{
    extract($ctx);

    /** @psalm-suppress UnresolvableInclude */
    return require $file;
}
