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

namespace Instride\Bundle\OpenDxpMonitoringBundle\Controller\Admin;

use OpenDxp\Controller\UserAwareController;
use OpenDxp\Logger;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Instride\Bundle\OpenDxpMonitoringBundle\Manager\RunnerManager;
use Instride\Bundle\OpenDxpMonitoringBundle\Reporter\ArrayReporter;

class HealthCheckController extends UserAwareController
{
    public function health(RunnerManager $runnerManager): JsonResponse
    {
        $user = $this->getOpenDxpUser();

        // Check permission
        if (!$user || !$user->isAdmin()) {
            Logger::error(
                'User {user} attempted to access the system health report results, but has no permission to do so.',
                ['user' => $user->getName()]
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
        $user = $this->getOpenDxpUser();

        // Check permission
        if (!$user || !$user->isAdmin()) {
            Logger::error(
                'User {user} attempted to access the system health status page, but has no permission to do so.',
                ['user' => $user->getName()]
            );

            throw $this->createAccessDeniedHttpException();
        }

        $reporter = new ArrayReporter(true);

        $runner = $runnerManager->getRunner();
        $runner->addReporter($reporter);
        $runner->run();

        $results = $reporter->getResults();

        return $this->render('@OpenDxpMonitoring/admin/health/status.html.twig', [
            'results' => $results,
        ]);
    }
}
