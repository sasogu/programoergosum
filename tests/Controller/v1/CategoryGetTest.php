<?php

namespace App\Tests\Controller\v1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response; // https://github.com/symfony/http-foundation/blob/master/Response.php

class CategoryGetTest extends WebTestCase
{
    private $client = null;

    protected function setUp()
    {
        $this->client = $this->createClient(['environment' => 'test']);
        $this->client->disableReboot();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->em->beginTransaction();
    }

    protected function tearDown()
    {
        $this->em->rollback();
    }



    public function test_getCategories_HTTP_OK()
    {
        $client = $this->client;
        $client->request('GET', '/v1/categories');

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $categories = json_decode($response->getContent(), true);
        $this->assertEquals(3, count($categories));

        $category_1 = $categories[0];
        $this->assertEquals(3, count($category_1));
        $this->assertEquals($category_1['title'], 'Categoría 1');
        $this->assertEquals($category_1['alias'], 'categoria-1');
        $this->assertEquals($category_1['description'], 'Descripción 1');
    }



    /**
     * @dataProvider provide_getCategoriesByAlias_HTTP_NOT_FOUND
     */
    public function test_getCategoriesByAlias_HTTP_NOT_FOUND(string $url = '')
    {
        $client = $this->client;
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $json = json_decode($response->getContent(), true);
        
        $this->assertEquals('Not found', $json['message']);
    }
    public function provide_getCategoriesByAlias_HTTP_NOT_FOUND()
    {
        yield ['/v1/categories/BAD-ALIAS'];
        yield ['/v1/categories/categoria-2']; // disabled
    }

    public function test_getCategoriesByAlias_HTTP_FOUND()
    {
        $client = $this->client;
        $client->request('GET', '/v1/categories/categoria-1');

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $category_1 = json_decode($response->getContent(), true);

        $this->assertEquals(7, count($category_1));
        $this->assertEquals($category_1['title'], 'Categoría 1');
        $this->assertEquals($category_1['alias'], 'categoria-1');
        $this->assertEquals($category_1['description'], 'Descripción 1');
        $this->assertEquals($category_1['metatitle'], 'Meta título de la categoría');
        $this->assertEquals($category_1['metadesc'], 'Meta descripción de la categoría 1');
        $this->assertEquals($category_1['metakey'], 'meta, palabras, categoría');
        $this->assertEquals($category_1['metaimage'], 'categoria.png');
    }

}
