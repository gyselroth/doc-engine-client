<?php

/**
 * Copyright (c) 2017-2018 gyselroth™  (http://www.gyselroth.net)
 *
 * @package \gyselroth\DocumentEngine
 * @link    http://www.gyselroth.com
 * @author  gyselroth™  (http://www.gyselroth.com)
 * @license Apache-2.0
 */

namespace Gyselroth\DocumentEngine\Client;


use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use GuzzleHttp\Exception\ServerException as HttpServerException;
use GuzzleHttp\Exception\RequestException;
use Gyselroth\DocumentEngine\Client\Auth\AuthException;

class ExceptionParser
{
    /**
     * @param  RequestException $exception
     * @return ConnectionException|ServerException|ClientException
     */
    public static function parseGuzzleException(RequestException $exception)
    {
        if ($exception instanceof ConnectException) {
            return new ConnectionException($exception->getMessage());
        }
        if ($exception instanceof HttpServerException) {
            return self::parseServerException($exception);
        }
        if ($exception instanceof HttpClientException) {
            return self::parseClientException($exception);
        }
        if ($exception instanceof RequestException) {
            return new ConnectionException($exception->getMessage());
        }
    }

    /**
     * @param  HttpServerException $exception
     * @return ServerException
     */
    private static function parseServerException(HttpServerException $exception)
    {
        if (!$exception->hasResponse()) {
            return new ServerException($exception->getMessage());
        }

        $response = $exception->getResponse();
        if ($response->getStatusCode() === 500) {
            $body = json_decode($response->getBody(), true);
            if ($body['exception']) {
                return new ServerException($response->getReasonPhrase() . ': ' . $body['exception']['message']);
            }
        }
        return new ServerException($response->getReasonPhrase());
    }

    /**
     * @param  HttpClientException $exception
     * @return ClientException
     */
    private static function parseClientException(HttpClientException $exception)
    {
        if (!$exception->hasResponse()) {
            return new ClientException($exception->getMessage());
        }

        $response = $exception->getResponse();
        if ($response->getStatusCode() === 401) {
            return new AuthException('unauthorized');
        }
        $body = json_decode($response->getBody(), true);
        if ($body['acceptable']) {
            return new ClientException($response->getReasonPhrase() . ' - Acceptable: ' . implode(', ', $body['acceptable']));
        }
        if ($body['supported']) {
            return new ClientException($response->getReasonPhrase() . ' - Supported: ' . implode(', ', $body['supported']));
        }

        return new ClientException($response->getReasonPhrase());
    }
}
