<?php

declare(strict_types=1);

namespace R3H6\Opentelemetry\Hooks;

use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

use function OpenTelemetry\Instrumentation\hook;

final class CacheHook extends AbstractHook
{
    public const NAME = 'typo3-cache';

    protected function instrument(): void
    {
        $pre1 = function (FrontendInterface $cache, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
            $span = $this->makeSpanBuilder($cache->getIdentifier(), $function, $class, $filename, $lineno)
                ->setAttribute('cache.key', $params[0])
                ->startSpan();

            Context::storage()->attach($span->storeInContext(Context::getCurrent()));
        };

        $post = function (FrontendInterface $cache, array $params, mixed $response, ?\Throwable $exception) {
            $this->endSpan($exception);
        };

        foreach (['set', 'get', 'has', 'remove'] as $f) {
            hook(class: FrontendInterface::class, function: $f, pre: $pre1, post: $post);
        }

        $pre2 = function (FrontendInterface $cache, array $params, string $class, string $function, ?string $filename, ?int $lineno) {
            $builder = $this->makeSpanBuilder($cache->getIdentifier(), $function, $class, $filename, $lineno);

            $tags = $params[0] ?? null;
            if (is_array($tags)) {
                $builder->setAttribute('cache.tags', implode(',', $tags));
            }
            if (is_string($tags)) {
                $builder->setAttribute('cache.tags', $tags);
            }

            $span = $builder->startSpan();

            Context::storage()->attach($span->storeInContext(Context::getCurrent()));
        };

        foreach (['flush', 'flushByTag', 'flushByTags', 'collectGarbage'] as $f) {
            hook(class: FrontendInterface::class, function: $f, pre: $pre2, post: $post);
        }
    }

    private function makeSpanBuilder(
        string $name,
        string $function,
        string $class,
        ?string $filename,
        ?int $lineno
    ): SpanBuilderInterface {
        return $this->instrumentation->tracer()
            ->spanBuilder(sprintf('cache::%s', $function))
            ->setSpanKind(SpanKind::KIND_INTERNAL)
            ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
            ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
            ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
            ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
            ->setAttribute('cache.operation', $function)
            ->setAttribute('cache.identifier', $name);
    }

}
