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

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use OpenDxp\Helper\FileSystemHelper;

class DiskUsage extends AbstractCheck
{
    protected const string IDENTIFIER = 'device:disk_usage';

    public function __construct(
        protected bool $skip,
        protected int $warningThreshold,
        protected int $criticalThreshold,
        protected string $path
    ) {}

    /**
     * @inheritDoc
     */
    public function check(): ResultInterface
    {
        if ($this->skip) {
            return new Skip('Check was skipped');
        }

        $df = (int) \floor(\disk_free_space($this->path));
        $dt = (int) \floor(\disk_total_space($this->path));
        $du = $dt - $df;
        $dp = ($du / $dt) * 100;

        $data = [
            'used' => FileSystemHelper::formatBytes($du),
            'free' => FileSystemHelper::formatBytes($df),
            'total' => FileSystemHelper::formatBytes($dt),
        ];

        if ($this->criticalThreshold > 0 && $dp >= $this->criticalThreshold) {
            return new Failure(\sprintf('Disk usage too high: %2d%%', $dp), $data);
        }

        if ($this->warningThreshold > 0 && $dp >= $this->warningThreshold) {
            return new Warning(\sprintf('Disk usage high: %2d%%', $dp), $data);
        }

        return new Success(\sprintf('Disk usage is %2d%%', $dp), $data);
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'Disk Usage';
    }
}
