<?php
/*
 * Fusio
 * An open source API management platform which helps to create innovative API solutions
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\Action\LifecycleInterface;
use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ConfigurableInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Exception\ConfigurationException;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Sandbox\Parser;
use PSX\Sandbox\SecurityManager;

/**
 * PhpSandbox
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class PhpSandbox extends PhpExecutorAbstract implements LifecycleInterface, ConfigurableInterface
{
    private Parser $parser;

    public function __construct(RuntimeInterface $runtime)
    {
        parent::__construct($runtime);

        $this->parser = new Parser($this->newSecurityManager());
    }

    public function getName(): string
    {
        return 'PHP-Sandbox';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        $action = $context->getAction()?->getName();
        if ($action === null) {
            throw new \RuntimeException('No action name available');
        }

        $file = $this->getActionFile($action);

        // it could be that the file is not longer available since we store the source code in the cache. I.e. if we
        // update a docker container the cache files are not transferred to the new container, because of this we check
        // here the file and write it again to the cache
        if (!is_file($file)) {
            $this->onCreate($action, $configuration);
        }

        return $this->execute($file, $request, $context);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newTextArea('code', 'Code', 'php', 'The PHP code of this action'));
    }

    public function onCreate(string $name, ParametersInterface $config): void
    {
        $file = $this->getActionFile($name);
        $code = $config->get('code') ?? throw new ConfigurationException('No code provided');
        $code = $this->parser->parse($code);

        file_put_contents($file, $code);
    }

    public function onUpdate(string $name, ParametersInterface $config): void
    {
        $file = $this->getActionFile($name);
        $code = $config->get('code') ?? throw new ConfigurationException('No code provided');
        $code = $this->parser->parse($code);

        file_put_contents($file, $code);
    }

    public function onDelete(string $name, ParametersInterface $config): void
    {
        $file = $this->getActionFile($name);

        if (is_file($file)) {
            unlink($file);
        }
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

    private function newSecurityManager(): SecurityManager
    {
        $securityManager = new SecurityManager();
        $securityManager->addAllowedClass('PSX\Sql\Builder');
        $securityManager->addAllowedClass('PSX\Sql\Reference');

        return $securityManager;
    }
}
