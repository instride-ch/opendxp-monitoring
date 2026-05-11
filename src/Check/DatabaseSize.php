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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use OpenDxp\Helper\FileSystemHelper;

class DatabaseSize extends AbstractCheck
{
    protected const string IDENTIFIER = 'device:database_size';

    public function __construct(
        protected bool $skip,
        protected int $warningThreshold,
        protected int $criticalThreshold,
        protected Connection $connection
    ) {}

    /**
     * @inheritDoc
     */
    public function check(): ResultInterface
    {
        if ($this->skip) {
            return new Skip('Check was skipped');
        }

        $size = $this->getDatabaseSize();

        if ($size === 0) {
            return new Failure('Database size could not be retrieved');
        }

        $data = [
            'size' => FileSystemHelper::formatBytes($size),
        ];

        if ($this->criticalThreshold > 0 && $size >= $this->criticalThreshold) {
            return new Failure(
                \sprintf('Database size is too high: %s', FileSystemHelper::formatBytes($size)),
                $data
            );
        }

        if ($this->warningThreshold > 0 && $size >= $this->warningThreshold) {
            return new Warning(
                \sprintf('Database size is high: %s', FileSystemHelper::formatBytes($size)),
                $data
            );
        }

        return new Success(
            \sprintf('Database size is %s', FileSystemHelper::formatBytes($size)),
            $data
        );
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'Database Size';
    }

    /**
     * Returns the size of the connected database
     */
    private function getDatabaseSize(): int
    {
        $query = <<<SQL
SELECT SUM(data_length + index_length) AS size
FROM information_schema.TABLES
GROUP BY table_schema
SQL;

        try {
            $size = $this->connection->fetchAllAssociative($query);
        } catch (Exception) {
            return 0;
        }

        return (int) ($size[0]['size'] ?? 0);
    }
}
