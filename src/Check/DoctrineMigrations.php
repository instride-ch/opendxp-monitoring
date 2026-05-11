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

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\ExecutedMigration;
use Doctrine\Migrations\Version\Version;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;

class DoctrineMigrations extends AbstractCheck
{
    protected const string IDENTIFIER = 'system:doctrine_migrations';

    /**
     * @var Version[]
     */
    protected array $availableVersions;

    /**
     * @var Version[]
     */
    protected array $migratedVersions;

    public function __construct(protected bool $skip, DependencyFactory $input)
    {
        $this->availableVersions = $this->getAvailableVersionsFromDependencyFactory($input);
        $this->migratedVersions = $this->getMigratedVersionsFromDependencyFactory($input);
    }

    /**
     * @inheritDoc
     */
    public function check(): ResultInterface
    {
        if ($this->skip) {
            return new Skip('Check was skipped');
        }

        $notMigratedVersions = \array_diff($this->availableVersions, $this->migratedVersions);

        if (! empty($notMigratedVersions)) {
            return new Failure(
                'Not all migrations applied',
                \array_values(\array_map('strval', $notMigratedVersions))
            );
        }

        $notAvailableVersions = \array_diff($this->migratedVersions, $this->availableVersions);

        if (! empty($notAvailableVersions)) {
            return new Failure(
                'Migrations applied which are not available',
                \array_values(\array_map('strval', $notAvailableVersions))
            );
        }

        return new Success(
            'All migrations are correctly applied',
            \array_values(\array_map('strval', $this->migratedVersions))
        );
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'Doctrine Migrations';
    }

    /**
     * @return Version[]
     */
    private function getAvailableVersionsFromDependencyFactory(DependencyFactory $dependencyFactory): array
    {
        $allMigrations = $dependencyFactory->getMigrationRepository()->getMigrations();

        return \array_map(
            static fn (AvailableMigration $availableMigration) => $availableMigration->getVersion(),
            $allMigrations->getItems()
        );
    }

    /**
     * @return Version[]
     */
    private function getMigratedVersionsFromDependencyFactory(DependencyFactory $dependencyFactory): array
    {
        $executedMigrations = $dependencyFactory->getMetadataStorage()->getExecutedMigrations();

        return \array_map(
            static fn (ExecutedMigration $executedMigration) => $executedMigration->getVersion(),
            $executedMigrations->getItems()
        );
    }
}
