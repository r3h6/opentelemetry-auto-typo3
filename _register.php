<?php

use OpenTelemetry\SDK\Sdk;
use R3H6\Opentelemetry\DoctrineDbalInstrumentation;
use R3H6\Opentelemetry\Typo3Instrumentation;

if (class_exists(Sdk::class) && Sdk::isInstrumentationDisabled(Typo3Instrumentation::NAME) === true) {
    return;
}

if (extension_loaded('opentelemetry') === false) {
    trigger_error('The opentelemetry extension must be loaded in order to autoload the OpenTelemetry Laravel auto-instrumentation', E_USER_WARNING);
    return;
}

Typo3Instrumentation::register();
DoctrineDbalInstrumentation::register();
