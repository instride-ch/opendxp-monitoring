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

namespace Instride\Bundle\OpenDxpMonitoringBundle\Command;

use Laminas\Diagnostics\Runner\Reporter\BasicConsole;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Instride\Bundle\OpenDxpMonitoringBundle\Manager\RunnerManager;

#[AsCommand(
    name: 'opendxp:monitoring:health-check',
    description: 'Run health checks for OpenDXP installation'
)]
class HealthCheckCommand extends Command
{

    public function __construct(private readonly RunnerManager $runnerManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = $this->runnerManager->getRunner();
        $runner->addReporter(new BasicConsole());
        $results = $runner->run();

        if (($results->getFailureCount() + $results->getWarningCount()) > 0) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
