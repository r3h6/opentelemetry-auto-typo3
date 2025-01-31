<?php

declare(strict_types=1);

namespace R3H6\Opentelemetry\Hooks;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

use function OpenTelemetry\Instrumentation\hook;

final class MiddlewareHook extends AbstractHook
{
    public const NAME = 'typo3-middleware';

    protected function instrument(): void
    {
        /**
         * Create a span for each psr-15 middleware that is executed.
         */
        hook(
            MiddlewareInterface::class,
            'process',
            pre: function (MiddlewareInterface $middleware, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
                // $span = $instrumentation->tracer()->spanBuilder(sprintf('middleware [%s]', $class))
                $span = $this->instrumentation->tracer()->spanBuilder(sprintf('%s::%s', $class, $function))
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                    ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                    ->startSpan();

                Context::storage()->attach($span->storeInContext(Context::getCurrent()));
            },
            post: function (MiddlewareInterface $middleware, array $params, ?ResponseInterface $response, ?\Throwable $exception) {
                $this->endSpan($exception);
            }
        );
    }
}
