<?php

namespace Modera\DirectBundle\Tests\Api;

use Modera\DirectBundle\Api\Api;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTest extends WebTestCase
{
    /**
     * Test Api->__toString() method.
     */
    public function test__toString()
    {
        $client = $this->createClient();
        $api = new Api($client->getContainer());

        $this->assertRegExp('/Actions/', $api->__toString());
    }
}
