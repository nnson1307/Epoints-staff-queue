<?php
namespace MyCore\Log\Handler;

use ApplicationInsights\Channel\Contracts\Message_Severity_Level;
use DaiDP\AppInsights\TelemetryClient;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class AppInsightsHandler
 * @package MyCore\Log\Handler
 * @author DaiDP
 * @since Feb, 2020
 */
class AppInsightsHandler extends AbstractProcessingHandler
{
    protected $appInsightLevel = [
        'DEBUG'     => Message_Severity_Level::INFORMATION,
        'INFO'      => Message_Severity_Level::INFORMATION,
        'NOTICE'    => Message_Severity_Level::WARNING,
        'WARNING'   => Message_Severity_Level::WARNING,
        'ERROR'     => Message_Severity_Level::ERROR,
        'CRITICAL'  => Message_Severity_Level::CRITICAL,
        'ALERT'     => Message_Severity_Level::CRITICAL,
        'EMERGENCY' => Message_Severity_Level::CRITICAL,
    ];

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        // TODO: Implement write() method.
        $telemetry = app()->get(TelemetryClient::class);
        $telemetry->trackMessage($record['formatted'], $this->mapErrorLevel($record['level_name']));
        $telemetry->flush();
    }

    /**
     * Lấy log level
     *
     * @param $monoLevelName
     * @return int
     */
    protected function mapErrorLevel($monoLevelName)
    {
        if (isset($this->appInsightLevel[$monoLevelName])) {
            $this->appInsightLevel[$monoLevelName];
        }

        return Message_Severity_Level::INFORMATION;
    }
}