<?php


namespace Amazon\PayV2\Model\Config\Source;


/**
 * Class AuthorizationMode
 * @package Amazon\PayV2\Model\Config\Source
 */
class AuthorizationMode
{
    const ASYNC = 'asynchronous';
    const SYNC = 'synchronous';
    const SYNC_THEN_ASYNC = 'synchronous_possible';
}
