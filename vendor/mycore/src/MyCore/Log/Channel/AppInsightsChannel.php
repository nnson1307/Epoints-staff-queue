<?php
namespace MyCore\Log\Channel;

use Illuminate\Log\ParsesLogConfiguration;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger as Monolog;
use MyCore\Log\Handler\AppInsightsHandler;

/**
 * Class AppInsightsChannel
 * @package MyCore\Log\Channel
 * @author DaiDP
 * @since Feb, 2020
 */
class AppInsightsChannel
{
    use ParsesLogConfiguration;

    /**
     * Cấu hình và khởi tạo channel log
     *
     * @param $config
     * @return Monolog
     */
    public function __invoke($config)
    {
        // TODO: Implement __invoke() method.
        $logHandler = new AppInsightsHandler($this->level($config), $config['bubble'] ?? true);
        $formatter  = $logHandler->getFormatter();

        if ($formatter instanceof LineFormatter) {
            $formatter->includeStacktraces(true);
        }

        return new Monolog($this->parseChannel($config), [$logHandler]);
    }

    /**
     * Get fallback log channel name.
     *
     * @return string
     */
    protected function getFallbackChannelName()
    {
        return app()->bound('env') ? app()->environment() : 'production';
    }
}