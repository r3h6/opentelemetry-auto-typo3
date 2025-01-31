<?php

declare(strict_types=1);

namespace R3H6\Opentelemetry\Tests\Unit\Hooks;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

abstract class AbstractTestCase extends UnitTestCase
{
    use ArraySubsetAsserts;

    protected ScopeInterface $scope;
    /** @var \ArrayObject<int, ImmutableSpan> */
    protected \ArrayObject $storage;

    public function setUp(): void
    {
        $this->storage = new \ArrayObject();
        $tracerProvider = new TracerProvider(
            new SimpleSpanProcessor(
                new InMemoryExporter($this->storage)
            )
        );

        $this->scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->activate();
    }

    public function tearDown(): void
    {
        $this->scope->detach();
    }
}
