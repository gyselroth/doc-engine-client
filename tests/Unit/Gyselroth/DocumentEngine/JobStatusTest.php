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

use Gyselroth\DocumentEngine\JobStatus;
use PHPUnit\Framework\TestCase;

class JobStatusTest extends TestCase
{
    public function testGetStatuses(): void
    {
        $statuses = [
            JobStatus::QUEUED,
            JobStatus::PROCESSING,
            JobStatus::DONE,
            JobStatus::FAILED,
            JobStatus::CANCELED,
            JobStatus::UNKNOWN
        ];

        $this->assertEquals($statuses, JobStatus::getStatuses());
    }
}
