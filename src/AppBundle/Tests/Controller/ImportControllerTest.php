<?php

namespace AppBundle\Controller\Tests;

use AppBundle\Tests\Controller\LoginWebTestCase;

class ImportControllerTest extends LoginWebTestCase
{
    public function testIndex()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/import');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
