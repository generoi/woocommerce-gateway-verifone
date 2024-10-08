<?php

namespace Lamia\HttpClient;

use GuzzleHttp6\Psr7\Response;
use Lamia\HttpClient\Exception\InvalidArgumentException;

interface HttpClientInterface
{
    /**
     * Send a GET request
     *
     * @param string $path       Request path
     * @param array  $parameters GET Parameters
     * @param array  $headers    Reconfigure the request headers for this call only
     *
     * @return Response
     */
    public function get($path, array $parameters = array(), array $headers = array());
    /**
     * Send a POST request
     *
     * @param string $path    Request path
     * @param mixed  $body    Request body
     * @param array  $headers Reconfigure the request headers for this call only
     *
     * @return Response
     */
    public function post($path, $body = null, array $headers = array());
    /**
     * Send a PATCH request
     *
     * @param string $path    Request path
     * @param mixed  $body    Request body
     * @param array  $headers Reconfigure the request headers for this call only
     *
     * @internal param array $parameters Request body
     * @return Response
     */
    public function patch($path, $body = null, array $headers = array());
    /**
     * Send a PUT request
     *
     * @param string $path    Request path
     * @param mixed  $body    Request body
     * @param array  $headers Reconfigure the request headers for this call only
     *
     * @return Response
     */
    public function put($path, $body, array $headers = array());
    /**
     * Send a DELETE request
     *
     * @param string $path    Request path
     * @param mixed  $body    Request body
     * @param array  $headers Reconfigure the request headers for this call only
     *
     * @return Response
     */
    public function delete($path, $body = null, array $headers = array());
    /**
     * Send a request to the server, receive a response,
     * decode the response and returns an associative array
     *
     * @param string $path       Request path
     * @param mixed  $body       Request body
     * @param string $httpMethod HTTP method to use
     * @param array  $headers    Request headers
     *
     * @return Response
     */
    public function request($path, $body, $httpMethod = 'GET', array $headers = array());
    /**
     * Change an option value.
     *
     * @param string $name  The option name
     * @param mixed  $value The value
     *
     * @throws InvalidArgumentException
     * @return void
     */
    public function setOption($name, $value);
    /**
     * Set HTTP headers
     *
     * @param  array $headers
     * @return void
     */
    public function setHeaders(array $headers);
}
