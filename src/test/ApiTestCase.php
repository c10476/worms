<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/6/19
 * Time: 18:38
 */

namespace worms\test;

use worms\core\Api;
use worms\core\Response;

class ApiTestCase extends TestCase
{
    /**
     * @desc   tearDown
     * @author storn
     */
    public function tearDown()
    {
        parent::tearDown();
        Response::create()->setContent(null);
    }

    /**
     * @desc   getResponse
     * @author storn
     *
     * @param Api $api
     *
     * @return Response
     */
    protected function getResponse(Api $api)
    {
        $api->main();

        return $api->getResponse();
    }
}