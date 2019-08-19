# ZlsPHP-Sentry

## 使用说明

请查阅 [文档](https://docs.73zls.com/zls-php/#)


## 快速上手

```php
// vim public/index.php

Zls::initialize()
->setExceptionHandle(
	new \Zls\Sentry\Exception\SentryException('http://9660d166c178476493ba42fc98a36ae9@192.168.3.135:9001/2')
)
```