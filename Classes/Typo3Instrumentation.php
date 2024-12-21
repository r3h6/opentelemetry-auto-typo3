<?php

namespace R3H6\Opentelemetry;

use Composer\InstalledVersions;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SemConv\TraceAttributes;
use R3H6\Opentelemetry\Hooks\ApplicationHook;
use R3H6\Opentelemetry\Hooks\CacheHook;
use R3H6\Opentelemetry\Hooks\ContentObjectHook;
use R3H6\Opentelemetry\Hooks\DataHandlerHook;
use R3H6\Opentelemetry\Hooks\MiddlewareHook;
use Symfony\Component\HttpFoundation\Request;

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;

use function OpenTelemetry\Instrumentation\hook;

final class Typo3Instrumentation
{
    public const NAME = 'typo3';

    public static function register(): void
    {
        $instrumentation = new CachedInstrumentation(
            'io.opentelemetry.contrib.php.' . self::NAME,
            null,
            'https://opentelemetry.io/schemas/1.24.0'
        );

        ApplicationHook::init($instrumentation);
        if (!Sdk::isInstrumentationDisabled(CacheHook::NAME)) {
            CacheHook::init($instrumentation);
        }
        if (!Sdk::isInstrumentationDisabled(ContentObjectHook::NAME)) {
            ContentObjectHook::init($instrumentation);
        }
        if (!Sdk::isInstrumentationDisabled(MiddlewareHook::NAME) && !InstalledVersions::isInstalled('open-telemetry/opentelemetry-auto-psr15')) {
            MiddlewareHook::init($instrumentation);
        }
        if (!Sdk::isInstrumentationDisabled(DataHandlerHook::NAME)) {
            DataHandlerHook::init($instrumentation);
        }

        self::rootSpan($instrumentation);
    }

    public static function rootSpan(CachedInstrumentation $instrumentation): void
    {
        hook(
            Bootstrap::class,
            'init',
            pre: static function (string $_, array $params, string $class, string $function, ?string $filename, ?int $lineno) use ($instrumentation) {

                $request = Environment::isCli() ? null : Request::createFromGlobals();

                $builder = $instrumentation->tracer()->spanBuilder('typo3')
                    ->setSpanKind(SpanKind::KIND_SERVER)
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                    ->setAttribute(TraceAttributes::CODE_LINENO, $lineno);
                $parent = Context::getCurrent();
                if ($request) {
                    //create http root span
                    $parent = Globals::propagator()->extract($request->headers->all());
                    $span = $builder
                        ->setParent($parent)
                        // ->setAttribute(TraceAttributes::URL_FULL, $request->getUri()->__toString())
                        ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $request->getMethod())
                        // ->setAttribute(TraceAttributes::HTTP_REQUEST_BODY_SIZE, $request->getHeaderLine('Content-Length'))
                        // ->setAttribute(TraceAttributes::URL_SCHEME, $request->getUri()->getScheme())
                        // ->setAttribute(TraceAttributes::URL_PATH, $request->getUri()->getPath())
                        // ->setAttribute(TraceAttributes::USER_AGENT_ORIGINAL, $request->getHeaderLine('User-Agent'))
                        // ->setAttribute(TraceAttributes::SERVER_ADDRESS, $request->getUri()->getHost())
                        // ->setAttribute(TraceAttributes::SERVER_PORT, $request->getUri()->getPort())
                        ->startSpan();
                    // $request = $request->withAttribute(SpanInterface::class, $span);
                } else {
                    $span = $builder->setSpanKind(SpanKind::KIND_INTERNAL)->startSpan();
                }
                Context::storage()->attach($span->storeInContext($parent));

                //register a shutdown function to end root span (@todo, ensure it runs _before_ tracer shuts down)
                register_shutdown_function(function () use ($span) {

                    $span->end();
                    $scope = Context::storage()->scope();
                    if (!$scope) {
                        return;
                    }
                    $scope->detach();
                });

            }
        );
    }
}
