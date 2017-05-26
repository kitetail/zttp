<?php

namespace Zttp;

use GuzzleHttp\Client;

class ZttpRequest
{
    /** @var  array */
    protected $options = [
        'http_errors' => false,
    ];

    /** @var  string */
    protected $bodyFormat = 'json';

    public static function new()
    {
        return new static();
    }

    /**
     * @return \Zttp\ZttpRequest
     */
    public function withoutRedirecting()
    {
        return tap($this, function () {
            return $this->options = $this->mergeOptions(['allow_redirects' => false]);
        });
    }

    /**
     * @return \Zttp\ZttpRequest
     */
    public function asJson()
    {
        return $this->bodyFormat('json')
                    ->contentType('application/json');
    }

    /**
     * @return \Zttp\ZttpRequest
     */
    public function asFormParams()
    {
        return $this->bodyFormat('form_params')
                    ->contentType('application/x-www-form-urlencoded');
    }

    /**
     * Set the body format.
     *
     * @param  string  $format
     *
     * @return \Zttp\ZttpRequest
     */
    public function bodyFormat($format)
    {
        return tap($this, function () use ($format) {
            $this->bodyFormat = $format;
        });
    }

    /**
     * Set the content type.
     *
     * @param  string  $contentType
     *
     * @return \Zttp\ZttpRequest
     */
    public function contentType($contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * Set the accept header.
     *
     * @param  string  $header
     *
     * @return \Zttp\ZttpRequest
     */
    public function accept($header)
    {
        return $this->withHeaders(['Accept' => $header]);
    }

    /**
     * Set the headers.
     *
     * @param  array  $headers
     *
     * @return \Zttp\ZttpRequest
     */
    public function withHeaders(array $headers)
    {
        return tap($this, function () use ($headers) {
            return $this->options = $this->mergeOptions(['headers' => $headers]);
        });
    }

    /**
     * Call the GET request.
     *
     * @param  string  $url
     * @param  array   $params
     *
     * @return \Zttp\ZttpResponse
     */
    public function get($url, array $params = [])
    {
        return $this->send('GET', $url, ['query' => $params]);
    }

    /**
     * Call the POST request.
     *
     * @param  string  $url
     * @param  array   $params
     *
     * @return \Zttp\ZttpResponse
     */
    public function post($url, array $params = [])
    {
        return $this->send('POST', $url, [$this->bodyFormat => $params]);
    }

    /**
     * Call the PATCH request.
     *
     * @param  string  $url
     * @param  array   $params
     *
     * @return \Zttp\ZttpResponse
     */
    public function patch($url, array $params = [])
    {
        return $this->send('PATCH', $url, [$this->bodyFormat => $params]);
    }

    /**
     * Call the PUT request.
     *
     * @param  string  $url
     * @param  array   $params
     *
     * @return \Zttp\ZttpResponse
     */
    public function put($url, array $params = [])
    {
        return $this->send('PUT', $url, [$this->bodyFormat => $params]);
    }

    /**
     * Call the DELETE request.
     *
     * @param  string  $url
     * @param  array   $params
     *
     * @return \Zttp\ZttpResponse
     */
    public function delete($url, array $params = [])
    {
        return $this->send('DELETE', $url, [$this->bodyFormat => $params]);
    }

    /**
     * Send a request.
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array   $options
     *
     * @return \Zttp\ZttpResponse
     */
    public function send($method, $url, array $options)
    {
        $options = $this->mergeOptions([
            'query' => $this->parseQueryParams($url),
        ], $options);

        return new ZttpResponse((new Client)->request($method, $url, $options));
    }

    /**
     * Merge request options.
     *
     * @param  array  ...$options
     *
     * @return array
     */
    protected function mergeOptions(...$options)
    {
        return array_merge_recursive($this->options, ...$options);
    }

    /**
     * Parse the query params.
     *
     * @param  string  $url
     *
     * @return array
     */
    protected function parseQueryParams($url)
    {
        return tap([], function (&$query) use ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }
}

/**
 * Call the given Closure with the given value then return the value.
 *
 * @param  mixed          $value
 * @param  callable|null  $callback
 *
 * @return mixed
 */
function tap($value, $callback = null)
{
    $callback($value);
    return $value;
}
