<?php

/**
 * Copyright (c) 2017-2018 gyselroth™  (http://www.gyselroth.net)
 *
 * @package \gyselroth\DocumentEngine
 * @link    http://www.gyselroth.com
 * @author  gyselroth™  (http://www.gyselroth.com)
 * @license Apache-2.0
 */

namespace Gyselroth\DocumentEngine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Gyselroth\DocumentEngine\Client;
use Gyselroth\DocumentEngine\JobStatus;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use \SplFileObject;
use Gyselroth\Helper\HelperFile;

class ClientTest extends TestCase
{
    protected static $tmpPath;

    public static function setUpBeforeClass()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        self::$tmpPath = sys_get_temp_dir() . '/phpunit' . uniqid();
        mkdir(self::$tmpPath);
    }

    public static function tearDownAfterClass()
    {
        HelperFile::rmdirRecursive(self::$tmpPath);
    }

    /**
    * @expectedException Gyselroth\DocumentEngine\Client\ClientException
    * @expectedExceptionMessage base url to document engine must be given
    */
    public function testConstructWithoutBaseUrl() : void
    {
        // Execute SUT
        new Client([]);
    }

    public function testGetApiInfo() : void
    {
        // Setup
        $data = ['version' => '1.0'];

        $mockHttpClient = self::setUpMockHttpClient('GET', 'http://example.com', $data);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals($data, $client->getApiInfo());
    }

    public function testValidateTemplate() : void
    {
        // Setup
        $data     = [];
        $template = new SplFileObject($this->getTempFile('template'));

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/validity/' . $template->getBasename(), $data);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT, Assertion
        $this->assertEquals($data, $client->validateTemplate($template));
    }

    public function testValidateTemplateTemplateRewind() : void
    {
        // Setup
        $template = $this->getFileMock('template', ['rewind']);
        $template->expects($this->once())
            ->method('rewind');

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/validity/' . $template->getBasename(), []);
        $client = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $client->validateTemplate($template);
    }

    public function testGetMergeFields() : void
    {
        // Setup
        $data     = [];
        $template = new SplFileObject($this->getTempFile('template'));

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/mergefields/' . $template->getBasename(), $data);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals($data, $client->getMergeFields($template));
    }

    public function testGetMergeFieldsTemplateRewind() : void
    {
        // Setup
        $template = $this->getFileMock('template', ['rewind']);
        $template->expects($this->once())
            ->method('rewind');

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/mergefields/' . $template->getBasename(), []);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $client->getMergeFields($template);
    }

    public function testGetJobStatus() : void
    {
        // Setup
        $data = [
            'status'    => 'done',
            'links'     => [
                [
                    'href'  => '/v1/jobs/123',
                    'rel'   => 'self'
                ],
                [
                    'href'  => '/v1/document/12345/result.pdf',
                    'rel'   => 'document'
                ]
            ]
        ];

        $mockHttpClient = self::setUpMockHttpClient('GET', 'http://example.com/v1/job/123', $data);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals(JobStatus::DONE, $client->getJobStatus('123'));
    }

    public function testGetJobStatusStatusMissing() : void
    {
        // Setup
        $data = [
            'links'     => [
                [
                    'href'  => '/v1/jobs/123',
                    'rel'   => 'self'
                ],
                [
                    'href'  => '/v1/document/12345/result.pdf',
                    'rel'   => 'document'
                ]
            ]
        ];

        $mockHttpClient = self::setUpMockHttpClient('GET', 'http://example.com/v1/job/123', $data);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals(JobStatus::UNKNOWN, $client->getJobStatus('123'));
    }

    public function testGetJobStatusStatusMalformed() : void
    {
        // Setup
        /** @noinspection NonSecureUniqidUsageInspection */
        $data = [
            'status'    => uniqid(),
            'links'     => [
                [
                    'href'  => '/v1/jobs/123',
                    'rel'   => 'self'
                ],
                [
                    'href'  => '/v1/document/12345/result.pdf',
                    'rel'   => 'document'
                ]
            ]
        ];

        $mockHttpClient = self::setUpMockHttpClient('GET', 'http://example.com/v1/job/123', $data);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals(JobStatus::UNKNOWN, $client->getJobStatus('123'));
    }

    public function testGetDocument() : void
    {
        // Setup
        $document = new SplFileObject($this->getTempFile('document'));
        $data     = $document->fread($document->getSize());

        $mockHttpClient = self::setUpMockHttpClient('GET', 'http://example.com/v1/document/123', $data, false);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals($data, $client->getDocument('123'));
    }

    public function testGeneratePdf() : void
    {
        // Setup
        $template = new SplFileObject($this->getTempFile('template'));
        $media    = [new SplFileObject($this->getTempFile('moredata'))];
        $data     = $template->fread($template->getSize());

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/document/pdf/' . $template->getBasename(), $data, false);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals($data, $client->generatePdf($template, [], [], $media));
    }

    /**
    * @expectedException Gyselroth\DocumentEngine\Client\ClientException
    * @expectedExceptionMessage json encoding error: Malformed UTF-8 characters, possibly incorrectly encoded
    */
    public function testGeneratePdfWithInvalidMergeData() : void
    {
        // Setup
        $template = new SplFileObject($this->getTempFile('template'));
        $media    = [new SplFileObject($this->getTempFile('moredata'))];
        /** @noinspection SubStrUsedAsArrayAccessInspection */
        $mergeData = [
            'field1'    => substr('Öö', 0, 1)
        ];

        $client         = new Client(['baseUrl' => 'http://example.com']);

        $client->generatePdf($template, $mergeData, [], $media);
    }

    /**
    * @expectedException Gyselroth\DocumentEngine\Client\ClientException
    * @expectedExceptionMessage invalid file given
    */
    public function testGeneratePdfWithInvalidFile() : void
    {
        // Setup
        $template = new SplFileObject($this->getTempFile('template'));
        $media    = ['test'];

        $client         = new Client(['baseUrl' => 'http://example.com']);

        $client->generatePdf($template, [], [], $media);
    }

    public function testGeneratePdfAsync() : void
    {
        // Setup
        $template = new SplFileObject($this->getTempFile('template'));
        $media    = [new SplFileObject($this->getTempFile('moredata'))];
        $data     = [
            'jobId' => 123,
            'links' => [
                [
                    'href'  => 'http://example.com/v1/job/123',
                    'rel'   => 'self'
                ]
            ]
        ];

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/document/pdf/' . $template->getBasename(), $data, true);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals(123, $client->generatePdfAsync($template, [], [], $media));
    }

    public function testGeneratePdfFilesRewind() : void
    {
        // Setup
        $template = $this->getFileMock('template', ['rewind']);
        $template->expects($this->once())->method('rewind');

        $media = [$this->getFileMock('moredata', ['rewind'])];
        $media[0]->expects($this->once())->method('rewind');

        $data = $template->fread($template->getSize());

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/document/pdf/' . $template->getBasename(), $data, false);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $client->generatePdf($template, [], [], $media);
    }

    public function testGenerateDocx() : void
    {
        // Setup
        $template = new SplFileObject($this->getTempFile('template'));
        $media    = [new SplFileObject($this->getTempFile('moredata'))];
        $data     = $template->fread($template->getSize());

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/document/docx/' . $template->getBasename(), $data, false);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals($data, $client->generateDocx($template, [], [], $media));
    }

    /**
    * @expectedException Gyselroth\DocumentEngine\Client\ClientException
    * @expectedExceptionMessage json encoding error: Malformed UTF-8 characters, possibly incorrectly encoded
    */
    public function testGenerateDocxWithInvalidMergeData() : void
    {
        // Setup
        $template = new SplFileObject($this->getTempFile('template'));
        $media    = [new SplFileObject($this->getTempFile('moredata'))];
        $mergeData = [
            'field1'    => substr('Öö', 0, 1)
        ];

        $client         = new Client(['baseUrl' => 'http://example.com']);

        $client->generateDocx($template, $mergeData, [], $media);
    }

    /**
    * @expectedException Gyselroth\DocumentEngine\Client\ClientException
    * @expectedExceptionMessage invalid file given
    */
    public function testGenerateDocxWithInvalidFile() : void
    {
        // Setup
        $template = new SplFileObject($this->getTempFile('template'));
        $media    = ['test'];

        $client         = new Client(['baseUrl' => 'http://example.com']);

        $client->generateDocx($template, [], [], $media);
    }

    public function testGenerateDocxFilesRewind() : void
    {
        // Setup
        $template = $this->getFileMock('template', ['rewind']);
        $template->expects($this->once())->method('rewind');

        $media = [$this->getFileMock('moredata', ['rewind'])];
        $media[0]->expects($this->once())->method('rewind');

        $data = $template->fread($template->getSize());

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/document/docx/' . $template->getBasename(), $data, false);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $client->generateDocx($template, [], [], $media);
    }

    public function testGenerateDocxAsync() : void
    {
        // Setup
        $template = new SplFileObject($this->getTempFile('template'));
        $media    = [new SplFileObject($this->getTempFile('moredata'))];
        $data     = [
            'jobId' => 123,
            'links' => [
                [
                    'href'  => 'http://example.com/v1/job/123',
                    'rel'   => 'self'
                ]
            ]
        ];

        $mockHttpClient = self::setUpMockHttpClient('POST', 'http://example.com/v1/document/docx/' . $template->getBasename(), $data, true);
        $client         = new Client(['baseUrl' => 'http://example.com'], $mockHttpClient);

        // Execute SUT
        $this->assertEquals(123, $client->generateDocxAsync($template, [], [], $media));
    }

    private function getFileMock(string $filename, array $methods)
    {
        return $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs([$this->getTempFile($filename)])
            ->setMethods($methods)
            ->getMock();
    }

    private function setUpMockHttpClient(string $expectMethod, string $expectUrl, $data, bool $json = true, int $statusCode = 200) : HttpClient
    {
        if ($json) {
            $data = json_encode($data);
        }
        $mock = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['request'])
            ->getMock();

        $mock->method('request')->willReturn(new Response($statusCode, [], $data));
        $mock->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo($expectMethod),
                $this->equalTo($expectUrl)
            );
        return $mock;
    }

    private function getTempFile($content)
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $path = self::$tmpPath . '/' . uniqid();
        file_put_contents($path, $content);
        return $path;
    }
}
