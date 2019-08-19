<?php
declare (strict_types=1);

namespace Zls\Sentry\Exception;


class SentryException implements \Zls_Exception_Handle
{
	$dsn = '';
    public function __construct($dsn,$options = [])
    {
		$this->dsn = $dsn;
    }

    public function handle(\Zls_Exception $exception)
    {
        \Sentry\init([
            'dsn'         =>$this->dsn,
            'server_name' => "local",
            'project_root'=>'./',
            "in_app_exclude"=>["vendor"]
        ]);
        \Sentry\captureException($exception);
        return $exception;
    }
}