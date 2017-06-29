<?php

namespace Zttp;

class Zttp
{
    static function __callStatic($method, $args)
    {
        return ZttpRequest::new()->{$method}(...$args);
    }
}

class ZttpRequest
{
    function __construct()
    {
        $this->beforeSendingCallback = function () {};
        $this->bodyFormat = 'json';
        $this->options = [
            'http_errors' => false,
        ];
    }

    static function new(...$args)
    {
        return new self(...$args);
    }

    function withoutRedirecting()
    {
        return tap($this, function ($request) {
            return $this->options = array_merge_recursive($this->options, [
                'allow_redirects' => false,
            ]);
        });
    }

    function withoutVerifying()
    {
        return tap($this, function ($request) {
            return $this->options = array_merge_recursive($this->options, [
                'verify' => false,
            ]);
        });
    }

    function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    function asFormParams()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    function bodyFormat($format)
    {
        return tap($this, function ($request) use ($format) {
            $this->bodyFormat = $format;
        });
    }

    function contentType($contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    function accept($header)
    {
        return $this->withHeaders(['Accept' => $header]);
    }

    function withHeaders($headers)
    {
        return tap($this, function ($request) use ($headers) {
            return $this->options = array_merge_recursive($this->options, [
                'headers' => $headers,
            ]);
        });
    }

    function beforeSending($callback)
    {
        $this->beforeSendingCallback = $callback;

        return $this;
    }

    function get($url, $queryParams = [])
    {
        return $this->send('GET', $url, [
            'query' => $queryParams,
        ]);
    }

    function post($url, $params = [])
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    function patch($url, $params = [])
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    function put($url, $params = [])
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    function delete($url, $params = [])
    {
        return $this->send('DELETE', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    function send($method, $url, $options)
    {
        return new ZttpResponse((new \GuzzleHttp\Client(['handler' => tap(\GuzzleHttp\HandlerStack::create(), function ($stack) {
            $stack->push(function ($handler) {
                return function ($request, $options) use ($handler) {
                    return $handler(tap($request, function ($request) {
                        ($this->beforeSendingCallback)(new PendingZttpRequest($request));
                    }), $options);
                };
            });
        })]))->request($method, $url, $this->mergeOptions([
            'query' => $this->parseQueryParams($url),
        ], $options)));
    }

    function mergeOptions(...$options)
    {
        return array_merge_recursive($this->options, ...$options);
    }

    function parseQueryParams($url)
    {
        return tap([], function (&$query) use ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }
}

class PendingZttpRequest
{
    function __construct($guzzleRequest)
    {
        $this->request = $guzzleRequest;
    }

    function body()
    {
        return (string) $this->request->getBody();
    }

    function headers()
    {
        return array_reduce(array_map(function ($values, $key) {
            return [$key, $values[0]];
        }, $this->request->getHeaders(), array_keys($this->request->getHeaders())), function ($headers, $pair) {
            return array_merge($headers, [
                $pair[0] => $pair[1],
            ]);
        }, []);
    }
}

class ZttpResponse
{
    function __construct($response)
    {
        $this->response = $response;
    }

    function body()
    {
        return (string) $this->response->getBody();
    }

    function json()
    {
        return json_decode($this->response->getBody(), true);
    }

    function header($header)
    {
        return $this->response->getHeaderLine($header);
    }

    function status()
    {
        return $this->response->getStatusCode();
    }

    function isSuccess()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    function isOk()
    {
        return $this->isSuccess();
    }

    function isRedirect()
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    function isClientError()
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    function isServerError()
    {
        return $this->status() >= 500;
    }

    function __call($method, $args)
    {
        return $this->response->{$method}(...$args);
    }
}

function tap($value, $callback) {
    $callback($value);
    return $value;
}
