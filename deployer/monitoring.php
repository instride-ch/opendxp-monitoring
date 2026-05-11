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

namespace Deployer;

desc('Runs the health report, if not disabled');
task('opendxp:monitoring:health-report', static function () {
    run('cd {{release_path}} && {{bin/console}} opendxp:monitoring:health-report');
})->select('disable!=monitoring_health_report');

task('opendxp:monitoring', [
    'opendxp:monitoring:health-report',
]);

after('deploy:success', 'opendxp:monitoring');
