<?php

declare(strict_types=1);

namespace R3H6\Opentelemetry\Log\Writer;

use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\LogRecord as OtelLogRecord;
use OpenTelemetry\API\Logs\Map\Psr3;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class OtelWriter extends \TYPO3\CMS\Core\Log\Writer\AbstractWriter
{
    public function writeLog(LogRecord $record)
    {
        $loggerProvider = GeneralUtility::makeInstance(LoggerProviderInterface::class);
        $logger = $loggerProvider->getLogger($record->getComponent());
        $logger->emit($this->convertRecord($record));
        return $this;
    }

    private function convertRecord(LogRecord $record): OtelLogRecord
    {
        $logRecord = (new OtelLogRecord())
            ->setTimestamp($this->convertMicrotimeToNanotime($record->getCreated()))
            ->setSeverityNumber(Psr3::severityNumber($record->getLevel()))
            ->setSeverityText($record->getLevel())
            ->setBody($record->getMessage());

        foreach ($record->getData() as $key => $value) {
            $logRecord->setAttribute($key, $value);
        }

        return $logRecord;
    }

    private function convertMicrotimeToNanotime(float $microtime): int
    {
        $seconds = (int)$microtime;
        $microseconds = ($microtime - $seconds) * 1e6;
        $nanoseconds = ($seconds * 1e9) + ($microseconds * 1e3);
        return (int)$nanoseconds;
    }

}
