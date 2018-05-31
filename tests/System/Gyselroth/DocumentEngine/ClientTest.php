<?php

/**
 * Copyright (c) 2017-2018 gyselroth™  (http://www.gyselroth.net)
 *
 * @package \gyselroth\DocumentEngine
 * @link    http://www.gyselroth.com
 * @author  gyselroth™  (http://www.gyselroth.com)
 * @license Apache-2.0
 */

namespace Gyselroth\DocumentEngine\Tests\System;

use PHPUnit\Framework\TestCase;
use Gyselroth\DocumentEngine\Client;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use \SplFileObject;

class ClientTest extends TestCase
{
    /** @var string */
    protected static $templatePath = './tests/data/example.docx';

    /** @var string */
    protected static $resultDocxPath = './tests/data/example_result.docx';

    /** @var string */
    protected static $resultPdfPath = './tests/data/example_result.pdf';

    /**
     * @return array
     */
    public function testGetConfigFromEnv(): array
    {
        /** @noinspection ReturnFalseInspection */
        $config = [
            'baseUrl'            => getenv('DOCENG_CLIENT_TEST_BASEURL'),
            'authenticationType' => getenv('DOCENG_CLIENT_TEST_AUTH_TYPE'),
            'username'           => getenv('DOCENG_CLIENT_TEST_BASIC_USER'),
            'password'           => getenv('DOCENG_CLIENT_TEST_BASIC_PASSWORD'),
            'token'              => getenv('DOCENG_CLIENT_TEST_BEARER_TOKEN')
        ];

        $this->assertNotFalse($config['baseUrl']);

        return $config;
    }

    /**
     * @depends testGetConfigFromEnv
     * @param $config
     */
    public function testGetApiInfo($config) : void
    {
        // Setup
        $data   = ['version' => '1.0'];
        $client = new Client($config);

        // Execute SUT (system under test)
        $this->assertEquals($data, $client->getApiInfo());
    }

    /**
     * @depends testGetConfigFromEnv
     * @param array              $config
     * @param SplFileObject|null $template
     */
    public function testValidateTemplate(array $config, $template = null) : void
    {
        // Setup
        $data = [];
        if (!$template) {
            $template = new SplFileObject(self::$templatePath);
        }
        $client = new Client($config);

        // Execute SUT
        $this->assertEquals($data, $client->validateTemplate($template));
    }

    /**
     * @depends testGetConfigFromEnv
     * @param array $config
     */
    public function testValidateTemplateTwice(array $config) : void
    {
        $template = new SplFileObject(self::$templatePath);
        $this->testValidateTemplate($config, $template);
        $this->testValidateTemplate($config, $template);
    }

    /**
     * @depends testGetConfigFromEnv
     * @param array              $config
     * @param SplFileObject|null $template
     */
    public function testGetMergeFields(array $config, $template = null) : void
    {
        // Setup
        $data = [
            'Schueler_Austrittsdatum',
            'Schueler_Geburtstag',
            'Schueler_Name',
            'Schueler_Ort',
            'Schueler_PLZ',
            'Schueler_Strasse_Nr',
            'Schueler_Vorname'
        ];
        if (!$template) {
            $template = new SplFileObject(self::$templatePath);
        }

        $client = new Client($config);

        // Execute SUT
        $this->assertEquals($data, $client->getMergeFields($template));
    }

    /**
     * @depends testGetConfigFromEnv
     * @param array $config
     */
    public function testGetMergefieldsTwice(array $config) : void
    {
        $template = new SplFileObject(self::$templatePath);
        $this->testGetMergeFields($config, $template);
        $this->testGetMergeFields($config, $template);
    }

    /**
     * @depends testGetConfigFromEnv
     * @param array $config
     */
    public function testGeneratePdf(array $config) : void
    {
        // Setup
        $pdf = new SplFileObject(self::$resultPdfPath);

        $data = [
            'Schueler_Austrittsdatum'   => '01.01.2018',
            'Schueler_Geburtstag'       => '01.01.1970',
            'Schueler_Name'             => 'Doe',
            'Schueler_Ort'              => 'Zürich',
            'Schueler_PLZ'              => '8047',
            'Schueler_Strasse_Nr'       => 'Alibsriederstrasse 226',
            'Schueler_Vorname'          => 'John',
        ];
        $template = new SplFileObject(self::$templatePath);
        $media    = [];

        $client = new Client($config);

        // Execute SUT
        $result = $client->generatePdf($template, $data, $media);

        // Assertions
        //$this->assertEquals($pdf->fread($pdf->getSize()), $result->read($result->getSize()));
    }

    /**
     * @depends testGetConfigFromEnv
     * @param array $config
     */
    public function testGenerateDocx(array $config) : void
    {
        // Setup
        $docx  = new SplFileObject(self::$resultDocxPath);
        $data = [
            'Schueler_Austrittsdatum'   => '01.01.2018',
            'Schueler_Geburtstag'       => '01.01.1970',
            'Schueler_Name'             => 'Doe',
            'Schueler_Ort'              => 'Zürich',
            'Schueler_PLZ'              => '8047',
            'Schueler_Strasse_Nr'       => 'Alibsriederstrasse 226',
            'Schueler_Vorname'          => 'John',
        ];
        $template = new SplFileObject(self::$templatePath);
        $media    = [];

        $client = new Client($config);

        // Execute SUT
        $result = $client->generateDocx($template, $data, $media);

        // Assertions
        //$this->assertEquals($docx->fread($docx->getSize()), $result->read($result->getSize()));
    }
}
