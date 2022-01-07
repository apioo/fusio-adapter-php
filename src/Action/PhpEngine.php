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
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * PhpEngine
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class PhpEngine extends ActionAbstract
{
    protected ?string $file;

    public function __construct(?string $file = null)
    {
        $this->file = $file;
    }

    public function setFile(?string $file)
    {
        $this->file = $file;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        $resp = runScript($this->file, [
            'request' => $request,
            'context' => $context,
            'connector' => $this->connector,
            'response' => $this->response,
            'processor' => $this->processor,
            'dispatcher' => $this->dispatcher,
            'logger' => $this->logger,
            'cache' => $this->cache,
        ]);

        if ($resp instanceof HttpResponseInterface) {
            return $resp;
        } else {
            return $this->response->build(204, [], []);
        }
    }
}

function runScript(string $file, array $ctx)
{
    extract($ctx);
    return require $file;
}
