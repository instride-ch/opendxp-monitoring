![OpenDXP Monitoring](docs/images/github_banner.png "OpenDXP Monitoring")

[![Software License](https://img.shields.io/badge/license-GPLv3-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Stable Version](https://img.shields.io/packagist/v/instride/opendxp-monitoring.svg?style=flat-square)](https://packagist.org/packages/instride/opendxp-monitoring)

This bundle provides a way to run a series of OpenDXP and application-related health checks. Each health check should
implement some application logic that you want to make sure always works. Another usage can be testing for specific
requirements, like the availability of PHP extensions.

## Available Checks
- **AppEnvironment:** Checks whether the application is running in production mode.
- **DiskUsage:** Checks how much space is being allocated on the disk.
- **DoctrineMigrations:** Checks whether all Doctrine Migrations have been migrated.
- **HostingSize:** Checks how much Disk space is used by this hosting.
- **DatabaseSize:** Checks how much space the Database uses on this hosting.
- **DatabaseTableSize:** Checks how much space each Database Table uses on this hosting.
- **HttpsConnection:** Checks whether the HTTPS encryption is enabled.
- **MySqlVersion:** Checks what MySQL version is configured.
- **PhpVersion:** Checks what PHP version is configured.
- **OpenDxpAreabricks:** Checks which Areabricks are installed within OpenDXP.
- **OpenDxpBundles:** Checks which Bundles are installed within OpenDXP.
- **OpenDxpElementCount:** Checks whether the count of OpenDXP Elements exceeds a certain threshold.
- **OpenDxpMaintenance:** Checks whether OpenDXP maintenance is enabled.
- **OpenDxpUsers:** Checks what OpenDXP Users are configured.
- **OpenDxpVersion:** Checks what OpenDXP Version is installed.

## Further Information
* [Installation & Bundle Configuration](docs/00-installation-configuration.md)
* [Adding and Running Checks](docs/01-adding-custom-checks.md)
* [Commands](docs/02-commands.md)
* [Defaults](docs/03-defaults.md)

## License
**instride AG**, Sandgruebestrasse 4, 6210 Sursee, Switzerland  
connect@instride.ch, [instride.ch](https://instride.ch)  
Copyright © 2026 instride AG. All rights reserved.

For licensing details please visit [LICENSE.md](LICENSE.md) 
