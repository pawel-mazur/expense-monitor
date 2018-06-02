<?php

namespace AppBundle\Controller\Tests;

use AppBundle\Tests\Controller\LoginWebTestCase;

class DefaultControllerTest extends LoginWebTestCase
{
    public function testIndex()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
