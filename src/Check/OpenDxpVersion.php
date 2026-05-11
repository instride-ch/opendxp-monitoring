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

namespace Instride\Bundle\OpenDxpMonitoringBundle\Check;

use Composer\InstalledVersions;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;

class OpenDxpVersion extends AbstractCheck
{
    protected const string IDENTIFIER = 'opendxp:version';

    public function __construct(protected bool $skip) {}

    /**
     * @inheritDoc
     */
    public function check(): ResultInterface
    {
        if ($this->skip) {
            return new Skip('Check was skipped');
        }

        $packageName = 'open-dxp/opendxp';
        $version = InstalledVersions::getPrettyVersion($packageName);

        return new Success(\sprintf('The system is running on OpenDXP %s', $version), [
            'semver' => $version,
            'reference' => InstalledVersions::getReference($packageName),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'OpenDXP Version';
    }
}
