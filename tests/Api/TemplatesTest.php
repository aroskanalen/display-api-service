<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Template;
use App\Tests\BaseTestTrait;

class TemplatesTest extends ApiTestCase
{
    use BaseTestTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/v1/templates?itemsPerPage=5', ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Template',
            '@id' => '/v1/templates',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 20,
            'hydra:view' => [
                '@id' => '/v1/templates?itemsPerPage=5&page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/v1/templates?itemsPerPage=5&page=1',
                'hydra:last' => '/v1/templates?itemsPerPage=5&page=4',
                'hydra:next' => '/v1/templates?itemsPerPage=5&page=2',
            ],
        ]);

        $this->assertCount(5, $response->toArray()['hydra:member']);

        // @TODO: resources: Object value found, but an array is required. In JSON it's an object but in the entity
        //        it's an key array? So this test will fail.
//        $this->assertMatchesResourceCollectionJsonSchema(Template::class);
    }

    public function testGetItem(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Template::class, []);

        $client->request('GET', $iri, ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => [
                '@vocab' => 'http://example.com/docs.jsonld#',
                'hydra' => 'http://www.w3.org/ns/hydra/core#',
                'title' => 'Template/title',
                'description' => 'Template/description',
                'created' => 'Template/created',
                'modified' => 'Template/modified',
                'modifiedBy' => 'Template/modifiedBy',
                'createdBy' => 'Template/createdBy',
                'resources' => 'Template/resources',
            ],
            '@type' => 'Template',
            '@id' => $iri,
        ]);

        // @TODO: resources: Object value found, but an array is required. In JSON it's an object but in the entity
        //        it's an key array? So this test will fail.
        //$this->assertMatchesResourceItemJsonSchema(Template::class);
    }
}
