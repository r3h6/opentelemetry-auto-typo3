<?php

use OpenTelemetry\SDK\Sdk;
use R3H6\Opentelemetry\Hooks\LogHook;

defined('TYPO3') or die();

(function () {
    if (!Sdk::isInstrumentationDisabled(LogHook::NAME)) {
        LogHook::instrument();
    }
})();
