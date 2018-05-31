gyselroth Document Engine Client
================================

Client library for the gyselroth Document Engine webservice


Minimum Requirements
--------------------

* PHP7.1 with extensions: json
* Build-tools: git, composer


Installation
------------

* Install via composer 

```sh
composer require gyselroth/document-engine-client:dev-trunk
```


Usage
-----
* Creating a minimal client
```php
$client = new Gyselroth\DocumentEngine\Client([
    'baseUrl' => 'https://docengine.example.com'
]);
```

* Creating a client using HTTP basic authentication
```php
$client = new Gyselroth\DocumentEngine\Client([
    'baseUrl' => 'https://docengine.example.com',
    'authenticationType' => 'basic',
    'username'    => 'foo',
    'password'    => 'bar'
]);
```

* Creating a client using Bearer authentication (e.g. oauth2, openid connect etc.)
```php
$client = new Gyselroth\DocumentEngine\Client([
    'baseUrl' => 'https://docengine.example.com',
    'authenticationType' => 'bearer',
    'token'    => 'aBCdeFGHIJKlmnOpq1RsTu',
    'password'    => 'bar'
]);
```

* The Document Engine Client lets you use own http client implementations. Any chosen implementation must implements the GuzzleHttp\ClientInterface interface.
```php
$client = new Gyselroth\DocumentEngine\Client([
    'baseUrl' => 'https://docengine.example.com'
], new MyHttpClient());
```


### Validate template
```php
$template = new \SplFileObject('template.docx');
$validity = $client->validateTemplate($template);
```


### Get merge-fields from template
```php
$template = new \SplFileObject('template.docx');
$mergefields = $client->getMergefields($template);
```


### Generate DOCX or PDF from template with given merge-data in a synchronous manner
```php
$template = new \SplFileObject('template.docx');
$mergeData = [
        'field1' => 'value1',
        'field2' => 'value2',
];
$docxStream = $client->generateDocx($template, $mergeData);
$pdfStream = $client->generatePdf($template, $mergeData);
```

*Note: The document generation methods return instances of `Psr\Http\Message\StreamInterface`. To read the stream into a file one can use a snippet like:*
```php
fwrite(fopen('result.pdf', 'w+'), $pdfStream->read($pdfStream->getSize()));
```


### Generate Docx or PDF from template with given merge-data in an asynchronous manner
```php
$template = new \SplFileObject('template.docx');
$mergeData = [
        'field1' => 'value1',
        'field2' => 'value2',
];
$jobId = $client->generateDocxAsync($template, $mergeData);
$jobId = $client->generatePdfAsync($template, $mergeData);
```


#### Check current status of a job
*Note: Gyselroth\DocumentEngine\Client::getJobStatus will - in every case, except for the annotated exceptions - return a string which equals to a constant from Gyselroth\DocumentEngine\JobStatus*
```php
$jobStatus = $client->getJobStatus($jobId);
```


#### Get an asynchronously generated document
```php
$documentStream = $client->getDocument($jobId);
```


Exceptions
----------
All public methods of the client can throw one of the following exceptions:
* `Gyselroth\DocumentEngine\Client\Auth\AuthException`
  * failed authorization (HTTP 401 Unauthorized), e.g. missing authentication
* `Gyselroth\DocumentEngine\Client\ClientException`
  * client side error (HTTP 4xx, but not HTTP 401 Unauthorized), e.g. HTTP 400 Bad Request
* `Gyselroth\DocumentEngine\Client\ServerException`
  * server side error (HTTP 5xx), e.g. HTTP 500 Internal Server Error
* `Gyselroth\DocumentEngine\Client\ConnectionException`
  * Networking error, e.g. server not reachable


Development Notes
-----------------

### Installation after checkout

```sh
composer update
```


Running Tests
-------------

* Unit tests
```sh
composer unittest
```

* System tests
```sh
export DOCENG_CLIENT_TEST_BASEURL=http://example.com
export DOCENG_CLIENT_TEST_AUTH_TYPE=basic
export DOCENG_CLIENT_TEST_BASIC_USER=test
export DOCENG_CLIENT_TEST_BASIC_PASSWORD=secret
composer systemtest
```

* All tests
```sh
export DOCENG_CLIENT_TEST_BASEURL=http://example.com
export DOCENG_CLIENT_TEST_AUTH_TYPE=basic
export DOCENG_CLIENT_TEST_BASIC_USER=test
export DOCENG_CLIENT_TEST_BASIC_PASSWORD=secret
composer test
```


History
-------

See `CHANGELOG.md`


Author and License
------------------

Copyright 2017-2018 gyselroth™ (http://www.gyselroth.com)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0":http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License. 


### Used Open Source Software

Open source packages used by the gyselroth Document Engine Client are copyright of their vendors, see related licenses within
the vendor packages.
