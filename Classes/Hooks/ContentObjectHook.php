<?php

namespace R3H6\Opentelemetry\Hooks;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

use function OpenTelemetry\Instrumentation\hook;

final class ContentObjectHook extends AbstractHook
{
    public const NAME = 'typo3-cobj';

    protected function instrument(): void
    {
        hook(
            ContentObjectRenderer::class,
            'cObjGetSingle',
            pre: function (ContentObjectRenderer $cObj, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
                $span = $this->instrumentation->tracer()->spanBuilder(sprintf('%s::%s', $class, $function))
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                    ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                    ->setAttribute('cObj.type', $params[0])
                    ->setAttribute('cObj.key', $params[2])
                    ->startSpan();

                Context::storage()->attach($span->storeInContext(Context::getCurrent()));
            },
            post: function (ContentObjectRenderer $cObj, array $params, ?string $content, ?\Throwable $exception) {
                $this->endSpan($exception, ['cObj.content' => $content]);
            }
        );
    }

}
