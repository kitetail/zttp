<?php

namespace Zttp;

class Zttp
{
    static $client;

    static function __callStatic($method, $args)
    {
        return ZttpRequest::new(static::client())->{$method}(...$args);
    }

    static function client(): \GuzzleHttp\Client
    {
        return static::$client ?: static::$client = new \GuzzleHttp\Client;
    }
}

class ZttpRequest
{
    function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
        $this->bodyFormat = 'json';
        $this->options = [
            'http_errors' => false,
        ];
    }

    static function new(...$args): ZttpRequest
    {
        return new self(...$args);
    }

    function withoutRedirecting(): ZttpRequest
    {
        return tap($this, function ($request) {
            return $this->options = array_merge_recursive($this->options, [
                'allow_redirects' => false
            ]);
        });
    }

    function asJson(): ZttpRequest
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    function asFormParams(): ZttpRequest
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    function bodyFormat(string $format): ZttpRequest
    {
        return tap($this, function ($request) use ($format) {
            $this->bodyFormat = $format;
        });
    }

    function contentType(string $contentType): ZttpRequest
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    function accept($header): ZttpRequest
    {
        return $this->withHeaders(['Accept' => $header]);
    }

    function withHeaders(array $headers): ZttpRequest
    {
        return tap($this, function ($request) use ($headers) {
            return $this->options = array_merge_recursive($this->options, [
                'headers' => $headers
            ]);
        });
    }

    function get(string $url, array $queryParams = []): ZttpResponse
    {
        return $this->send('GET', $url, [
            'query' => $queryParams,
        ]);
    }

    function post(string $url, array $params = []): ZttpResponse
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    function patch(string $url, array $params = []): ZttpResponse
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    function put(string $url, array $params = []): ZttpResponse
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    function delete(string $url, array $params = []): ZttpResponse
    {
        return $this->send('DELETE', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    function send(string $method, string $url, array $options): ZttpResponse
    {
        return new ZttpResponse($this->client->request($method, $url, $this->mergeOptions([
            'query' => $this->parseQueryParams($url),
        ], $options)));
    }

    function mergeOptions(array ...$options): array
    {
        return array_merge_recursive($this->options, ...$options);
    }

    function parseQueryParams(string $url): array
    {
        return tap([], function (&$query) use ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }
}

class ZttpResponse
{
    function __construct($response)
    {
        $this->response = $response;
    }

    function body(): string
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

    function isSuccess(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    function isOk(): bool
    {
        return $this->isSuccess();
    }

    function isRedirect(): bool
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    function isClientError(): bool
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    function isServerError(): bool
    {
        return $this->status() >= 500;
    }

    function __call($method, $args)
    {
        return $this->response->{$method}(...$args);
    }
}

function tap($value, \Closure $callback) {
    $callback($value);
    return $value;
}
