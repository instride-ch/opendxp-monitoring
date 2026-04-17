<?php

declare(strict_types=1);

/**
 * OpenDxp Monitor
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  2026 instride AG (https://instride.ch)
 * @license    https://github.com/instride-ch/opendxp-monitor/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */
namespace Instride\Bundle\OpenDxpMonitorBundle;

use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\OpenDxpBundleAdminClassicInterface;
use OpenDxp\Extension\Bundle\Traits\BundleAdminClassicTrait;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Instride\Bundle\OpenDxpMonitorBundle\Check\CheckInterface;
use Instride\Bundle\OpenDxpMonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;

class OpenDxpMonitorBundle extends AbstractOpenDxpBundle implements OpenDxpBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    /**
     * {@inheritDoc}
     */
    public function getNiceName(): string
    {
        return 'OpenDxp Monitor';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return 'Monitor your OpenDxp installation';
    }

    /**
     * {@inheritDoc}
     */
    public function getCssPaths(): array
    {
        return [
            '/bundles/opendxpmonitor/opendxp/css/icons.css',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getJsPaths(): array
    {
        return [
            '/bundles/OpenDxpmonitor/OpenDxp/js/startup.js',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        if (\method_exists($container, 'registerForAutoconfiguration')) {
            $container
                ->registerForAutoconfiguration(CheckInterface::class)
                ->addTag('OpenDxp_monitor.check');
        }

        $container->addCompilerPass(new CheckTagCompilerPass());
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackageName(): string
    {
        return 'instride/opendxp-monitor';
    }
}
