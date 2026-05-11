# Installation & Bundle Configuration

### Install with Composer

```
composer require instride/opendxp-monitoring:^1.0
```

### Loading the Bundle

Register the bundle in your `config/bundles.php` file to enable it.

```php
<?php

return [
    // ...
    Instride\Bundle\OpenDxpMonitoringBundle\OpenDxpMonitoringBundle::class => ['all' => true],
];
```

### Report Configuration

This bundle allows you to send a health report to a custom REST endpoint.

```yaml
opendxp_monitoring:
    report:

        # API key for health report endpoint.
        api_key: '<YOUR_RANDOM_BEARER_TOKEN>'

        # Default health report API endpoint to send data to.
        default_endpoint: 'https://api.example.com/health-report'

        # Environment description for the instance (e.g. 'PRD', 'STG', 'DEV').
        instance_environment: 'PRD'
```

> **Note:** The health report is triggered with the command `opendxp:monitoring:health-report`.
> Learn more about the available commands [here](02-commands.md).
