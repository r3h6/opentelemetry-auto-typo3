<?php

defined('TYPO3') or die();

use R3H6\Opentelemetry\Sdk;

(function () {

    if (!Sdk::isInstrumentationDisabled('typo3-log')) {
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                \R3H6\Opentelemetry\Log\Writer\OtelWriter::class => [],
            ],
        ];
    }
})();
