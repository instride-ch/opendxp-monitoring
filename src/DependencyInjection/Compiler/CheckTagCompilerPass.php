<?php

declare(strict_types=1);

/**
 * OpenDXP Monitoring.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  2026 instride AG (https://instride.ch)
 * @license    https://github.com/instride-ch/opendxp-monitoring/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\OpenDxpMonitoringBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CheckTagCompilerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('opendxp_monitoring.check') as $id => $tags) {
            foreach ($tags as $attributes) {
                $alias = empty($attributes['alias']) ? $id : $attributes['alias'];
                $enabledParamName = \sprintf('opendxp_monitoring.checks.%s.enabled', $alias);

                if ($container->hasParameter($enabledParamName) && $container->getParameter($enabledParamName)) {
                    $runnerDefinition = $container->getDefinition('opendxp_monitoring.runner');
                    $runnerDefinition->addMethodCall('addCheck', [new Reference($id), $alias]);
                }
            }
        }
    }
}
