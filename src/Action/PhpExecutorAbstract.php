<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Adapter\Php\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ExcludedInterface;
use Fusio\Engine\RequestInterface;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * PhpExecutorAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
abstract class PhpExecutorAbstract extends ActionAbstract implements ExcludedInterface
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

/**
 * @param array<string, mixed> $ctx
 */
function runScript(string $file, array $ctx): mixed
{
    extract($ctx);

    /** @psalm-suppress UnresolvableInclude */
    return require $file;
}
