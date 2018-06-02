<?php

namespace AppBundle\Controller\Tests;

use AppBundle\Tests\Controller\LoginWebTestCase;

class TagControllerTest extends LoginWebTestCase
{
    public function testIndex()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/tag');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
