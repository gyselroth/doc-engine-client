<?php

/**
 * Copyright (c) 2017-2018 gyselroth™  (http://www.gyselroth.net)
 *
 * @package \gyselroth\DocumentEngine
 * @link    http://www.gyselroth.com
 * @author  gyselroth™  (http://www.gyselroth.com)
 * @license Apache-2.0
 */

namespace Gyselroth\DocumentEngine;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use Gyselroth\DocumentEngine\Client\ExceptionParser;
use Gyselroth\DocumentEngine\Client\ClientException;
use Gyselroth\DocumentEngine\Client\Auth\AuthFactory;
use Gyselroth\Http\FileHandler\SingleFileHandler;
use \SplFileObject;

class Client
{
    /** @var Client\Auth\Basic|Client\Auth\Bearer|Client\Auth\None */
    private $authentication;

    /** @var string */
    private $baseUrl;

    /** @var HttpClient */
    private $httpClient;

    /**
     * Constructor
     *
     * @param  array                $config
     * @param  ClientInterface|null $httpClient
     * @uses   $config['baseUrl'] as base url for the api
     * @uses   $config['authenticationType'] to set up authentication
     * @throws ClientException
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     */
    public function __construct(array $config, ClientInterface $httpClient = null)
    {
        if (empty($config['baseUrl'])) {
            throw new ClientException('base url to document engine must be given');
        }

        $this->baseUrl        = $config['baseUrl'];
        $this->httpClient     = $httpClient ?: new HttpClient();
        $this->authentication = AuthFactory::GetAuth($config);
    }

    /**
     * @return  array       $result
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function getApiInfo() : array
    {
        $response = $this->request('GET');

        return json_decode($response->getBody(), true);
    }

    /**
     * @param   SplFileObject   $template
     * @return  array           $result
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function validateTemplate(SplFileObject $template) : array
    {
        $endpoint = '/v1/validity/' . $template->getBasename();
        $response = $this->postFile($endpoint, $template);

        // TODO: parse response
        return json_decode($response->getBody(), true);
    }

    /**
     * @param   SplFileObject  $template
     * @return  array          $result
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function getMergeFields(SplFileObject $template) : array
    {
        $endpoint = '/v1/mergefields/' . $template->getBasename();
        $response = $this->postFile($endpoint, $template);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param   string          $jobId
     * @return  string          Constant from \Gyselroth\DocumentEngine\JobStatus
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function getJobStatus(string $jobId) : string
    {
        $endpoint = '/v1/job/' . $jobId;
        $response = $this->request('GET', $endpoint);
        $body     = json_decode($response->getBody(), true);

        return (\array_key_exists('status', $body) && \in_array($body['status'], JobStatus::STATUSES, true))
            ? $body['status']
            : JobStatus::UNKNOWN;
    }

    /**
     * @param   string          $jobId
     * @return  StreamInterface
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function getDocument(string $jobId) : StreamInterface
    {
        $endpoint = '/v1/document/' . $jobId;
        $response = $this->request('GET', $endpoint);

        return $response->getBody();
    }

    /**
     * @param   SplFileObject $template
     * @param   array         $mergeData
     * @param   array         $options
     * @param   array         $additionalFiles
     * @return  StreamInterface
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function generateDocx(SplFileObject $template, array $mergeData, array $options = [], array $additionalFiles = []) : StreamInterface
    {
        $endpoint = $this->getEndpointByTemplateName($template);
        $files    = self::setupRequestFiles($template, $additionalFiles);
        $response = $this->multipartRequest($endpoint, $mergeData, $options, $files);

        return $response->getBody();
    }

    /**
     * @param   SplFileObject $template
     * @param   array         $mergeData
     * @param   array         $options
     * @param   array         $additionalFiles
     * @return  string
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function generateDocxAsync(SplFileObject $template, array $mergeData, array $options = [], array $additionalFiles = []) : string
    {
        $options['async'] = true;
        $response = $this->generateDocx($template, $mergeData, $options, $additionalFiles);

        return (json_decode($response, true))['jobId'];
    }

    /**
     * @param   SplFileObject $template
     * @param   array         $mergeData
     * @param   array         $options
     * @param   array         $additionalFiles
     * @return  StreamInterface
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function generatePdf(SplFileObject $template, array $mergeData, array $options = [], array $additionalFiles = []) : StreamInterface
    {
        $endpoint = $this->getEndpointByTemplateName($template, 'document/pdf');
        $files    = self::setupRequestFiles($template, $additionalFiles);
        $response = $this->multipartRequest($endpoint, $mergeData, $options, $files);

        return $response->getBody();
    }

    /**
     * @param   SplFileObject $template
     * @param   array         $mergeData
     * @param   array         $options
     * @param   array         $additionalFiles
     * @return  string
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    public function generatePdfAsync(SplFileObject $template, array $mergeData, array $options = [], array $additionalFiles = []) : string
    {
        $options['async'] = true;
        $response = $this->generatePdf($template, $mergeData, $options, $additionalFiles);

        return (json_decode($response, true))['jobId'];
    }

    /**
     * @param  SplFileObject $template
     * @param  string        $route
     * @param  string        $apiVersion
     * @return string
     */
    private function getEndpointByTemplateName(SplFileObject $template, string $route = 'document/docx', string $apiVersion = 'v1'): string
    {
        return "/$apiVersion/$route/" . $template->getBasename();
    }

    /**
     * @param SplFileObject $template
     * @param array         $additionalFiles
     * @return array
     */
    private static function setupRequestFiles(SplFileObject $template, array $additionalFiles): array
    {
        return array_merge([$template], array_values($additionalFiles));
    }

    /**
     * @param  string $endpoint
     * @param  SplFileObject  $file
     * @return Response
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    private function postFile(string $endpoint, SplFileObject $file) : Response
    {
        // Ensure file is rewound before building request
        $file->rewind();

        return $this->request('POST', $endpoint, [
            'body' => $file,
            'headers' => [
                'Content-Type' => mime_content_type($file->getRealPath())
            ]
        ]);
    }

    /**
     * @param  string $endpoint
     * @param  array  $mergeData
     * @param  array  $options
     * @param  array  $files
     * @return Response
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    private function multipartRequest(string $endpoint, array $mergeData, array $options, array $files) : Response
    {
        $data = [
            [
                'name'      => 'mergeData',
                'contents'  => self::jsonEncode($mergeData)
            ],
            [
                'name'      => 'options',
                'contents'  => self::jsonEncode($options)
            ]
        ];

        foreach ($files as $file) {
            if (!$file instanceof SplFileObject) {
                throw new ClientException('invalid file given');
            }
            // Ensure file is rewound before building request
            $file->rewind();

            $data[] = [
                    'name'  => $file->getBasename(),
                    'filename'  => $file->getBasename(),
                    'contents' => $file
            ];
        }
        return $this->request('POST', $endpoint, ['multipart' => $data]);
    }

    /**
     * @param  string $method
     * @param  string $endpoint
     * @param  array  $options
     * @return Response
     * @throws \Gyselroth\DocumentEngine\Client\Auth\AuthException
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     * @throws \Gyselroth\DocumentEngine\Client\ServerException
     * @throws \Gyselroth\DocumentEngine\Client\ConnectionException
     */
    private function request(string $method, string $endpoint = '', array $options = []) : Response
    {
        $options = $this->authentication->addAuthentication($options);
        try {
            $response = $this->httpClient->request($method, $this->baseUrl . $endpoint, $options);
        } catch (RequestException $exception) {
            throw ExceptionParser::parseGuzzleException($exception);
        }
        return $response;
    }

    /**
     * @param  mixed    $value
     * @param  int      $options
     * @param  int      $depth
     * @return string
     * @throws \Gyselroth\DocumentEngine\Client\ClientException
     */
    private static function jsonEncode($value, int $options = 0, int $depth = 512) : string
    {
        $result = \json_encode($value, $options, $depth);
        if (false === $result) {
            throw new ClientException('json encoding error: '. \json_last_error_msg());
        }
        return $result;
    }
}
