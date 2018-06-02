<?php

namespace AppBundle\Controller\Tests;

use AppBundle\Tests\Controller\LoginWebTestCase;

class OperationControllerTest extends LoginWebTestCase
{
    public function testIndex()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/operation');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
