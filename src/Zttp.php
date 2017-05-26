<?php

namespace Zttp;

/**
 * Class     Zttp
 *
 * @package  Zttp
 *
 * @method  static  \Zttp\ZttpRequest   withoutRedirecting()
 * @method  static  \Zttp\ZttpRequest   asFormParams()
 * @method  static  \Zttp\ZttpRequest   asJson()
 * @method  static  \Zttp\ZttpRequest   bodyFormat(string $format)
 * @method  static  \Zttp\ZttpRequest   contentType(string $contentType)
 * @method  static  \Zttp\ZttpRequest   accept(string $header)
 * @method  static  \Zttp\ZttpRequest   withHeaders(array $headers)
 *
 * @method  static  \Zttp\ZttpResponse  get(string $url, array $params = [])
 * @method  static  \Zttp\ZttpResponse  post(string $url, array $params = [])
 * @method  static  \Zttp\ZttpResponse  patch(string $url, array $params = [])
 * @method  static  \Zttp\ZttpResponse  put(string $url, array $params = [])
 * @method  static  \Zttp\ZttpResponse  delete(string $url, array $params = [])
 * @method  static  \Zttp\ZttpResponse  send(string $method, string $url, array $options)
 */
class Zttp
{
    public static function __callStatic($method, $args)
    {
        return ZttpRequest::new()->{$method}(...$args);
    }
}
