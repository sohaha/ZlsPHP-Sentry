<?php
declare (strict_types=1);

namespace Zls\Sentry\Exception;

use InvalidArgumentException;
use Raven_Autoloader;
use Raven_Client;
use Z;
use Zls_Logger_Dispatcher;

require_once __DIR__ . '/../Autoloader.php';
include __DIR__ . '/../Processor/SanitizeDataProcessor.php';

class SentryException implements \Zls_Exception_Handle
{
    private $dsn = '';
    private $options;
    private $global;

    public function __construct($dsn = '', $options = [])
    {
        if ($dsn) {
            $this->dsn = $dsn;
        } else {
            $this->dsn = Z::config('ini.sentry.dsn');
        }
        $this->options = $options;
        $this->global  = Z::config('ini.sentry.global');
        if (!$this->dsn && $this->global) {
            Zls_Logger_Dispatcher::initialize();
            throw new InvalidArgumentException('Invalid Sentry DSN');
        }
    }

    public function handle(\Zls_Exception $exception)
    {
        $config = Z::config();
        if (!$config->getShowError()) {
            $ajax      = Z::isAjax();
            $errorCode = $exception->getCode();
            $this->captureException($exception);

            return $ajax ? Z::json($errorCode, $errorCode) : $errorCode;
        } elseif ($this->global) {// 默认情况只在关闭debug下开启
            $this->captureException($exception);
        }

        return $exception;
    }

    private function captureException(\Exception $exception)
    {
        if (!$this->dsn) {
            return;
        }
        Raven_Autoloader::register();
        $client = new Raven_Client($this->dsn, array_merge(['app_path' => './', 'excluded_app_paths' => ['vendor']], $this->options));
        $client->getIdent($client->captureException($exception));
    }
}
