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

namespace Instride\Bundle\OpenDxpMonitorBundle\Controller\Admin;

use OpenDxp\Controller\UserAwareController;
use OpenDxp\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Instride\Bundle\OpenDxpMonitorBundle\Manager\RunnerManager;
use Instride\Bundle\OpenDxpMonitorBundle\Reporter\ArrayReporter;

class HealthCheckController extends UserAwareController
{
    public function health(RunnerManager $runnerManager): JsonResponse
    {
        $opendxpUser = $this->getOpenDxpUser();

        // Check rights
        if (!$opendxpUser || !$opendxpUser->isAdmin()) {
            Logger::error(
                'User {user} attempted to access the system health report results, but has no permission to do so.',
                ['user' => $opendxpUser->getName()]
            );

            throw $this->createAccessDeniedHttpException();
        }

        $reporter = new ArrayReporter();

        $runner = $runnerManager->getRunner();
        $runner->addReporter($reporter);
        $runner->run();

        $results = $reporter->getResults();

        return $this->json($results);
    }

    public function status(RunnerManager $runnerManager): Response
    {
        $opendxpUser = $this->getOpenDxpUser();

        // Check rights
        if (!$opendxpUser || !$opendxpUser->isAdmin()) {
            Logger::error(
                'User {user} attempted to access the system health status page, but has no permission to do so.',
                ['user' => $opendxpUser->getName()]
            );

            throw $this->createAccessDeniedHttpException();
        }

        $reporter = new ArrayReporter(true);

        $runner = $runnerManager->getRunner();
        $runner->addReporter($reporter);
        $runner->run();

        $results = $reporter->getResults();

        return $this->render('@OpenDxpMonitor/admin/health/status.html.twig', [
            'results' => $results,
        ]);
    }
}
