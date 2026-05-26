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

namespace Instride\Bundle\OpenDxpMonitoringBundle;

use Composer\InstalledVersions;
use Instride\Bundle\OpenDxpMonitoringBundle\Check\CheckInterface;
use Instride\Bundle\OpenDxpMonitoringBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
use Instride\Bundle\OpenDxpMonitoringBundle\DependencyInjection\OpenDxpMonitoringExtension;
use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\OpenDxpBundleAdminClassicInterface;
use OpenDxp\Extension\Bundle\Traits\BundleAdminClassicTrait;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class OpenDxpMonitoringBundle extends AbstractOpenDxpBundle implements OpenDxpBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container): void
    {
        if (\method_exists($container, 'registerForAutoconfiguration')) {
            $container
                ->registerForAutoconfiguration(CheckInterface::class)
                ->addTag('opendxp_monitoring.check');
        }

        $container->addCompilerPass(new CheckTagCompilerPass());
    }

    /**
     * @inheritDoc
     */
    public function getNiceName(): string
    {
        return 'OpenDXP Monitoring';
    }

    /**
     * @inheritDoc
     */
    protected function getComposerPackageName(): string
    {
        return 'instride/opendxp-monitoring';
    }

    public function getVersion(): string
    {
        $packageName = $this->getComposerPackageName();

        if (\class_exists(InstalledVersions::class) && InstalledVersions::isInstalled($packageName)) {
            return InstalledVersions::getVersion($packageName);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCssPaths(): array
    {
        return [
            '/bundles/opendxpmonitoring/css/icons.css',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getJsPaths(): array
    {
        return [
            '/bundles/opendxpmonitoring/js/startup.js',
        ];
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new OpenDxpMonitoringExtension();
        }

        return $this->extension ?: null;
    }
}
