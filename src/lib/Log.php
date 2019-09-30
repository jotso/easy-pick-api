<?php
namespace Lib;

use Monolog\Processor\IntrospectionProcessor;

class Log
{
    public static $logger;

    private static function init()
    {
        if (self::$logger == null) {
            $logger = new \Monolog\Logger('Easy-pick-api');
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
//            $logger->pushProcessor(new IntrospectionProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../../logs/app.log', \Monolog\Logger::DEBUG));
            self::$logger = $logger;
        }
    }

    public static function info($message, $context = [])
    {
        self::init();
        self::$logger->info($message, $context);
    }

    public static function critical($message, $context = [])
    {
        self::init();
        self::$logger->critical($message, $context);
    }
}
