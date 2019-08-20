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

    public function __construct($dsn = '', $options = [])
    {
        if ($dsn) {
            $this->dsn = $dsn;
        } else {
            $this->dsn = Z::config('ini.sentry.dsn');
        }
        $this->options = $options;
        if (!$this->dsn) {
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

            return $ajax ? Z::json($errorCode, $errorCode) : $errorCode;
        }
        if ($this->dsn) {
            Raven_Autoloader::register();
            $client = new Raven_Client($this->dsn, array_merge(['app_path' => './', 'excluded_app_paths' => ['vendor']], $this->options));
            $client->getIdent($client->captureException($exception));
        }

        return $exception;
    }
}
