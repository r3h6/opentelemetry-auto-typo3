<?php

declare(strict_types=1);

namespace R3H6\Opentelemetry\Hooks;

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;

abstract class AbstractHook
{
    final public static function init(CachedInstrumentation $instrumentation): void
    {
        $instance = new static($instrumentation);
        $instance->instrument();
    }

    final public function __construct(protected CachedInstrumentation $instrumentation) {}

    abstract protected function instrument(): void;

    final protected function endSpan(?\Throwable $exception, array $attributes = []): void
    {
        $scope = Context::storage()->scope();
        if (!$scope) {
            return;
        }
        $scope->detach();
        $span = Span::fromContext($scope->context());
        if ($exception) {
            $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
        } else {
            foreach ($attributes as $key => $value) {
                $span->setAttribute($key, $value);
            }
        }

        $span->end();
    }
}
