<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Feed;
use App\Entity\FeedSource;
use App\Entity\Slide;
use App\Feed\RssFeedType;
use App\Service\FeedService;
use App\Tests\BaseTestTrait;

class FeedSourceTest extends ApiTestCase
{
    use BaseTestTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/v1/feed-sources?itemsPerPage=10', ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/FeedSource',
            '@id' => '/v1/feed-sources',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:view' => [
                '@id' => '/v1/feed-sources?itemsPerPage=10',
                '@type' => 'hydra:PartialCollectionView',
            ],
        ]);

        $this->assertCount(1, $response->toArray()['hydra:member']);
    }

    public function testGetItem(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(FeedSource::class, []);

        $client->request('GET', $iri, ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => [
                '@vocab' => 'http://example.com/docs.jsonld#',
                'hydra' => 'http://www.w3.org/ns/hydra/core#',
            ],
            '@type' => 'FeedSource',
            '@id' => $iri,
        ]);
    }
}
