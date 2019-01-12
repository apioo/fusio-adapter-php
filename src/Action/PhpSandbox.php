<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use PSX\Sandbox\Parser;

/**
 * PhpSandbox
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class PhpSandbox extends PhpEngine implements LifecycleInterface
{
    public function getName()
    {
        return 'PHP-Sandbox';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $file = $this->getActionFile($context->getAction()->getName());

        // it could be that the file is not longer available since we store the
        // source code in the cache. I.e. if we update a docker container the
        // cache files are not transferred to the new container, because of
        // this we check here the file and write it again to the cache
        if (!is_file($file)) {
            $this->onCreate($context->getAction()->getName(), $configuration);
        }

        $this->setFile($file);

        return parent::handle($request, $configuration, $context);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newTextArea('code', 'Code', 'php', 'Click <a ng-click="help.showDialog(\'help/action/php.md\')">here</a> for more information.'));
    }

    public function onCreate($name, ParametersInterface $config)
    {
        $file = $this->getActionFile($name);
        $code = (new Parser())->parse($config->get('code'));

        file_put_contents($file, $code);
    }

    public function onUpdate($name, ParametersInterface $config)
    {
        $file = $this->getActionFile($name);
        $code = (new Parser())->parse($config->get('code'));

        file_put_contents($file, $code);
    }

    public function onDelete($name, ParametersInterface $config)
    {
        $file = $this->getActionFile($name);

        if (is_file($file)) {
            unlink($file);
        }
    }

    private function getActionFile($name)
    {
        if (defined('PSX_PATH_CACHE')) {
            $basePath = PSX_PATH_CACHE;
        } else {
            $basePath = sys_get_temp_dir();
        }

        return $basePath . '/sandbox_' . substr(md5($name), 0, 8) . '.php';
    }
}
