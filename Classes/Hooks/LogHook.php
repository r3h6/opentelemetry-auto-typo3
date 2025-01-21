<?php

namespace R3H6\Opentelemetry\Hooks;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class LogHook
{
    public const NAME = 'typo3-log';

    public static function instrument(): void
    {
        $loglevel = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('opentelemetry', 'instrumentation_log_level');
        if ($loglevel === 'auto') {
            self::configure($GLOBALS['TYPO3_CONF_VARS']['LOG']);
        } else {
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][$loglevel][\R3H6\Opentelemetry\Log\Writer\OtelWriter::class] = [];
        }
    }

    private static function configure(array &$configuration)
    {
        foreach ($configuration as $key => &$value) {
            if (is_array($value)) {
                self::configure($value);
            }
            if ($key === 'writerConfiguration' && is_array($value)) {
                foreach (array_keys($value) as $logLevel) {
                    if (!isset($value[$logLevel][\R3H6\Opentelemetry\Log\Writer\OtelWriter::class])) {
                        $value[$logLevel][\R3H6\Opentelemetry\Log\Writer\OtelWriter::class] = [];
                    }
                }
            }
        }
    }
}
