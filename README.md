# OpenTelemetry TYPO3 auto-instrumentation

Please read https://opentelemetry.io/docs/instrumentation/php/automatic/ for instructions on how to
install and configure the extension and SDK.

## Overview
Auto-instrumentation hooks are registered via composer, and spans will automatically be created for selected method on:

- `Psr\Http\Server\MiddlewareInterface`
- `TYPO3\CMS\Core\Cache\Frontend\FrontendInterface`
- `TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer`
- `TYPO3\CMS\Core\Core\ApplicationInterface`
- `TYPO3\CMS\Core\DataHandling\DataHandler`

Additionaly a OTEL log writer is added to every configured log.

## Configuration

The extension can be disabled via [runtime configuration](https://opentelemetry.io/docs/instrumentation/php/sdk/#configuration):

```shell
OTEL_PHP_DISABLED_INSTRUMENTATIONS=typo3
```

## Additional

Events: `open-telemetry/opentelemetry-auto-psr14`

Database: `r3h6/opentelemetry-auto-doctrine-dbal`
