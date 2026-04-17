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

namespace Instride\Bundle\OpenDxpMonitorBundle\Check;

use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use OpenDxp\Extension\Bundle\OpenDxpBundleManager;

class OpenDxpBundles extends AbstractCheck
{
    protected const IDENTIFIER = 'opendxp:bundles';

    public function __construct(protected bool $skip, protected OpenDxpBundleManager $openDxpBundleManager) {}

    public function check(): ResultInterface
    {
        if ($this->skip) {
            return new Skip('Check was skipped');
        }

        $bundles = [];

        foreach ($this->openDxpBundleManager->getActiveBundles() as $bundle) {
            $bundles[] = [
                'identifier' => $this->openDxpBundleManager->getBundleIdentifier($bundle),
                'name' => $bundle->getNiceName(),
                'version' => $bundle->getVersion(),
                'is_enabled' => true,
                'is_installed' => $this->openDxpBundleManager->isInstalled($bundle),
            ];
        }

        return new Success(\sprintf('There are %s OpenDxp bundles in the system', \count($bundles)), $bundles);
    }

    public function getLabel(): string
    {
        return 'OpenDxp Bundles';
    }
}
