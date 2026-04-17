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

namespace Instride\Bundle\OpenDxpMonitorBundle\Command;

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
use Instride\Bundle\OpenDxpMonitorBundle\Manager\RunnerManager;
use Instride\Bundle\OpenDxpMonitorBundle\Reporter\ArrayReporter;

#[AsCommand(
    name: 'opendxp:monitor:health-report',
    description: 'Run all checks and send them to your statistics receiving endpoint.'
)]
class HealthReportCommand extends Command
{

    public function __construct(
        private string $reportEndpoint,
        private string $apiKey,
        private string $instanceEnvironment,
        private array $systemConfig,
        private string $secret,
        private HttpClientInterface $httpClient,
        private RunnerManager $runnerManager
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
        $instanceId = 'opendxp-instance';
        $hostDomain = 'opendxp-monitoring-test';

        if (null === $instanceId) {
            $output->writeln('<comment>Please define the secret parameter.</comment>');
            return Command::FAILURE;
        }

        if (empty($hostDomain)) {
            $output->writeln('<comment>Please define the main domain.</comment>');
            return Command::FAILURE;
        }

        $checkReporter = new ArrayReporter(
            true,
            $input->getOption('exclude'),
            $input->getOption('include')
        );

        $runner = $this->runnerManager->getRunner();
        $runner->addReporter($checkReporter);
        $runner->run();

        $results = $checkReporter->getResults();

        // Transform to match curl format
        $checks = array_map(function($check) {
            return [
                'name' => $check['check_id'],
                'status' => $check['status_code'] === 0 ? 'OK' : 'KO',
                'message' => $check['message'],
            ];
        }, $results);

        echo json_encode($checks);

        $payload = [
            'instance_id' => $instanceId,
            'checks' => $checks,
            'metadata' => [
                'host_domain' => $hostDomain,
                'instance_environment' => $this->instanceEnvironment,
            ]
        ];

        echo "\nSending payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";

        try {
            $response = $this->httpClient->request('PUT', $input->getOption('endpoint'), [
                'json' => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
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
            $output->writeln(
                sprintf(
                    '<error>Sending the data to the endpoint failed!</error> – %s',
                    $exception->getMessage()
                )
            );

            return Command::FAILURE;
        }

        $output->writeln(sprintf('<question>%s: %s</question>', $payload['status'], $payload['message']));

        return $response->getStatusCode() === 200 ? Command::SUCCESS : Command::FAILURE;
    }

    private function getInstanceId(): ?string
    {
        if (empty($this->secret)) {
            return null;
        }

        try {
            $instanceId = sha1(substr($this->secret, 3, -3));
        } catch (\Exception) {
            $instanceId = null;
        }

        return $instanceId;
    }
}
