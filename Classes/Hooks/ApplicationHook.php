<?php

namespace R3H6\Opentelemetry\Hooks;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use TYPO3\CMS\Core\Core\ApplicationInterface;

use function OpenTelemetry\Instrumentation\hook;

final class ApplicationHook extends AbstractHook
{
    protected function instrument(): void
    {
        hook(
            ApplicationInterface::class,
            'run',
            pre: function (ApplicationInterface $app, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
                $span = $this->instrumentation->tracer()->spanBuilder(sprintf('%s::%s', $class, $function))
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                    ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                    ->startSpan();

                Context::storage()->attach($span->storeInContext(Context::getCurrent()));
            },
            post: function (ApplicationInterface $app, array $params, mixed $return, ?\Throwable $exception) {
                $this->endSpan($exception);
            }
        );
    }
}
