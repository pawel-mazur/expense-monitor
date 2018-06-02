<?php

namespace AppBundle\Controller\Tests;

use AppBundle\Tests\Controller\LoginWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContactControllerTest extends LoginWebTestCase
{
    public function testIndex()
    {
        $this->logIn();

        $crawler = $this->client->request('GET', '/contact');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
