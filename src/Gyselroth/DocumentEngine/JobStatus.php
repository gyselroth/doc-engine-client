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

// @todo Use native enum types (will probably be introduced w/ PHP 7.2)
class JobStatus {

    // Job is in queue, waiting to be processed
    public const QUEUED  = 'queued';

    // Job is currently processed by the engine
    public const PROCESSING = 'processing';

    // Job has completed successfully
    public const DONE = 'done';

    // Job has failed due to some server-side error
    public const FAILED = 'failed';

    // Job was canceled by user or the engine
    public const CANCELED = 'canceled';

    // Job status is not known
    public const UNKNOWN = 'unknown';

    public const STATUSES = [
        self::QUEUED,
        self::PROCESSING,
        self::DONE,
        self::FAILED,
        self::CANCELED,
        self::UNKNOWN
    ];

    /**
     * @return array
     */
    public static function getStatuses(): array
    {
        return self::STATUSES;
    }
}
