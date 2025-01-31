<?php

declare(strict_types=1);

namespace R3H6\Opentelemetry\Hooks;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use TYPO3\CMS\Core\DataHandling\DataHandler;

use function OpenTelemetry\Instrumentation\hook;

final class DataHandlerHook extends AbstractHook
{
    public const NAME = 'typo3-datahandler';

    protected function instrument(): void
    {

        hook(
            DataHandler::class,
            'process_datamap',
            pre: function (DataHandler $dataHandler, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
                $span = $this->instrumentation->tracer()->spanBuilder(sprintf('%s::%s', $class, $function))
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                    ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                    ->startSpan();

                Context::storage()->attach($span->storeInContext(Context::getCurrent()));
            },
            post: function (DataHandler $dataHandler, array $params, mixed $return, ?\Throwable $exception) {
                $this->endSpan($exception);
            }
        );

        hook(
            DataHandler::class,
            'process_cmdmap',
            pre: function (DataHandler $dataHandler, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
                $span = $this->instrumentation->tracer()->spanBuilder(sprintf('%s::%s', $class, $function))
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                    ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                    ->startSpan();

                Context::storage()->attach($span->storeInContext(Context::getCurrent()));
            },
            post: function (DataHandler $dataHandler, array $params, mixed $return, ?\Throwable $exception) {
                $this->endSpan($exception);
            }
        );
    }
}
