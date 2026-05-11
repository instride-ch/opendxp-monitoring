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

use OpenDxp\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Instride\Bundle\OpenDxpMonitoringBundle\Manager\RunnerManager;
use Instride\Bundle\OpenDxpMonitoringBundle\Reporter\ArrayReporter;

#[AsCommand(
    name: 'opendxp:monitoring:health-report',
    description: 'Run all checks and send them to your statistics receiving endpoint.'
)]
class HealthReportCommand extends AbstractCommand
{

    public function __construct(
        private readonly string $reportEndpoint,
        private readonly string $apiKey,
        private readonly string $instanceEnvironment,
        private readonly array $systemConfig,
        private readonly string $secret,
        private readonly HttpClientInterface $httpClient,
        private readonly RunnerManager $runnerManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command runs all checks and sends them to the defined report endpoint.')
            ->addOption(
                'endpoint',
                null,
                InputOption::VALUE_REQUIRED,
                'Overwrite the default endpoint to send the report data to.',
                $this->reportEndpoint
            )
            ->addOption(
                'exclude',
                'ex',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'List any task alias that you want to exclude from execution.'
            )
            ->addOption(
                'include',
                'in',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'List any task alias that you want to execute.'
            );
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $instanceId = $this->getInstanceId();
        $hostDomain = $this->systemConfig['general']['domain'] ?? null;

        if (null === $instanceId) {
            $this->io->comment('Please defined the secret parameter.');

            return Command::FAILURE;
        }

        if (empty($hostDomain)) {
            $this->io->comment('Please define the main domain.');

            return Command::FAILURE;
        }

        $checkReporter = new ArrayReporter(
            false,
            $input->getOption('exclude'),
            $input->getOption('include')
        );

        $runner = $this->runnerManager->getRunner();
        $runner->addReporter($checkReporter);
        $runner->run();

        try {
            $response = $this->httpClient->request('PUT', $input->getOption('endpoint'), [
                'auth_bearer' => $this->apiKey,
                'json' => [
                    'instance_id' => $instanceId,
                    'checks' => $checkReporter->getResults(),
                    'metadata' => [
                        'host_domain' => $hostDomain,
                        'instance_environment' => $this->instanceEnvironment,
                    ],
                ],
            ]);
            $payload = $response->toArray();
        } catch (
            TransportExceptionInterface |
            ClientExceptionInterface |
            DecodingExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface $exception
        ) {
            $this->io->error(
                \sprintf(
                    'Sending the data to the endpoint failed! – %s',
                    $exception->getMessage()
                )
            );

            return Command::FAILURE;
        }

        $this->io->info(\sprintf('%s: %s', $payload['status'], $payload['message']));

        return $response->getStatusCode() === 200 ? Command::SUCCESS : Command::FAILURE;
    }

    private function getInstanceId(): ?string
    {
        if (empty($this->secret)) {
            return null;
        }

        try {
            $instanceId = \sha1(\substr($this->secret, 3, -3));
        } catch (\Exception) {
            $instanceId = null;
        }

        return $instanceId;
    }
}
