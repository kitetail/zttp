<?php

namespace Zttp;

use Psr\Http\Message\ResponseInterface;

class ZttpResponse
{
    const HTTP_OK = 200;
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /** @var  \Psr\Http\Message\ResponseInterface */
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Get the response body as a string.
     *
     * @return string
     */
    public function body()
    {
        return (string) $this->response->getBody();
    }

    /**
     * Decode the json response.
     *
     * @param  bool  $assoc
     * @param  int   $options
     *
     * @return mixed
     */
    public function json($assoc = true, $options = 0)
    {
        return json_decode($this->body(), $assoc, $options);
    }

    /**
     * Get a response's header.
     *
     * @param  string  $header
     *
     * @return string
     */
    public function header($header)
    {
        return $this->response->getHeaderLine($header);
    }

    /**
     * Get the response's status code.
     *
     * @return int
     */
    public function status()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Check if the response is success (alias).
     *
     * @see isSuccess
     *
     * @return bool
     */
    public function isOk()
    {
        return $this->isSuccess();
    }

    /**
     * Check if the response is success.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status() >= static::HTTP_OK
            && $this->status() < static::HTTP_MULTIPLE_CHOICES;
    }

    /**
     * Check if the response is a redirection.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return $this->status() >= static::HTTP_MULTIPLE_CHOICES
            && $this->status() < static::HTTP_BAD_REQUEST;
    }

    /**
     * Check if the response is a client error.
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->status() >= static::HTTP_BAD_REQUEST
            && $this->status() < static::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Check if the response is a server error.
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->status() >= static::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function __call($method, array $args)
    {
        return $this->response->{$method}(...$args);
    }
}
