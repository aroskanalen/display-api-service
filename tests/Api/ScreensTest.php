<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Playlist;
use App\Entity\PlaylistScreenRegion;
use App\Entity\Screen;
use App\Entity\ScreenLayout;
use App\Entity\ScreenLayoutRegions;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class ScreensTest extends ApiTestCase
{
    // @TODO: Last test testUnlinkRegionPlaylist fails when refresh is used.
    //use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/v1/screens?itemsPerPage=5', ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Screen',
            '@id' => '/v1/screens',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/v1/screens?itemsPerPage=5&page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/v1/screens?itemsPerPage=5&page=1',
                'hydra:last' => '/v1/screens?itemsPerPage=5&page=20',
                'hydra:next' => '/v1/screens?itemsPerPage=5&page=2',
            ],
        ]);

        $this->assertCount(5, $response->toArray()['hydra:member']);

        // @TODO: hydra:member[0].dimensions: Object value found, but an array is required
//        $this->assertMatchesResourceCollectionJsonSchema(Screen::class);
    }

    public function testGetItem(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Screen::class, []);

        $client->request('GET', $iri, ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => [
                '@vocab' => 'http://example.com/docs.jsonld#',
                'hydra' => 'http://www.w3.org/ns/hydra/core#',
                'title' => 'Screen/title',
                'description' => 'Screen/description',
                'size' => 'Screen/size',
                'created' => 'Screen/created',
                'modified' => 'Screen/modified',
                'modifiedBy' => 'Screen/modifiedBy',
                'createdBy' => 'Screen/createdBy',
                'layout' => 'Screen/layout',
                'location' => 'Screen/location',
                'regions' => 'Screen/regions',
                'inScreenGroups' => 'Screen/inScreenGroups',
                'dimensions' => 'Screen/dimensions',
            ],
            '@type' => 'Screen',
            '@id' => $iri,
        ]);
    }

    public function testCreateScreen(): void
    {
        $client = static::createClient();

        $layoutIri = $this->findIriBy(ScreenLayout::class, []);

        $response = $client->request('POST', '/v1/screens', [
            'json' => [
                'title' => 'Test screen 42',
                'description' => 'This is a test screen',
                'size' => '65',
                'modifiedBy' => 'Test Tester',
                'createdBy' => 'Test Hansen',
                'layout' => $layoutIri,
                'location' => 'M2.42',
                'dimensions' => [
                    'width' => 1920,
                    'height' => 1080,
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => [
                '@vocab' => 'http://example.com/docs.jsonld#',
                'hydra' => 'http://www.w3.org/ns/hydra/core#',
                'title' => 'Screen/title',
                'description' => 'Screen/description',
                'size' => 'Screen/size',
                'created' => 'Screen/created',
                'modified' => 'Screen/modified',
                'modifiedBy' => 'Screen/modifiedBy',
                'createdBy' => 'Screen/createdBy',
                'layout' => 'Screen/layout',
                'location' => 'Screen/location',
                'regions' => 'Screen/regions',
                'inScreenGroups' => 'Screen/inScreenGroups',
                'dimensions' => 'Screen/dimensions',
            ],
            '@type' => 'Screen',
            'title' => 'Test screen 42',
            'description' => 'This is a test screen',
            'size' => '65',
            'modifiedBy' => 'Test Tester',
            'createdBy' => 'Test Hansen',
            'layout' => $layoutIri,
            'location' => 'M2.42',
            'dimensions' => [
                'width' => 1920,
                'height' => 1080,
            ],
        ]);
        $this->assertMatchesRegularExpression('@^/v\d/\w+/([A-Za-z0-9]{26})$@', $response->toArray()['@id']);

        // @TODO: dimensions: Object value found, but an array is required
//        $this->assertMatchesResourceItemJsonSchema(Screen::class);
    }

    public function testCreateInvalidScreen(): void
    {
        static::createClient()->request('POST', '/v1/screens', [
            'json' => [
                'title' => 123456789,
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'The input data is misformatted.',
        ]);
    }

    public function testUpdateScreen(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Screen::class, []);

        $client->request('PUT', $iri, [
            'json' => [
                'title' => 'Updated title',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@type' => 'Screen',
            '@id' => $iri,
            'title' => 'Updated title',
        ]);
    }

    public function testDeleteScreen(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Screen::class, []);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);

        $ulid = static::getContainer()->get('App\Utils\Utils')->getUlidFromIRI($iri);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Screen::class)->findOneBy(['id' => $ulid])
        );
    }

    public function testGetPlaylistsInScreenRegion(): void
    {
        $client = static::createClient();
        $utils = static::getContainer()->get('App\Utils\Utils');

        $iri = $this->findIriBy(Screen::class, []);
        $screenUlid = $utils->getUlidFromIRI($iri);

        $iri = $this->findIriBy(ScreenLayoutRegions::class, []);
        $regionUlid = $utils->getUlidFromIRI($iri);

        $url = '/v1/screens/'.$screenUlid.'/regions/'.$regionUlid.'/playlists?itemsPerPage=5';
        $client->request('GET', $url, ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Playlist',
            '@id' => '/v1/playlists',
            '@type' => 'hydra:Collection',
            'hydra:view' => [
                '@id' => $url,
                '@type' => 'hydra:PartialCollectionView',
            ],
        ]);
    }

    public function testLinkRegionPlaylist(): void
    {
        $client = static::createClient();
        $utils = static::getContainer()->get('App\Utils\Utils');

        $iri = $this->findIriBy(Screen::class, []);
        $screenUlid = $utils->getUlidFromIRI($iri);

        $iri = $this->findIriBy(Playlist::class, []);
        $playlistUlid = $utils->getUlidFromIRI($iri);

        $iri = $this->findIriBy(ScreenLayoutRegions::class, []);
        $regionsUlid = $utils->getUlidFromIRI($iri);

        $url = '/v1/screens/'.$screenUlid.'/regions/'.$regionsUlid.'/playlists/'.$playlistUlid;
        $client->request('PUT', $url, [
            'json' => [],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        /** @var Playlist $playlist */
        $link = static::getContainer()->get('doctrine')->getRepository(PlaylistScreenRegion::class)->findOneBy([
            'playlist' => $playlistUlid,
            'screen' => $screenUlid,
            'region' => $regionsUlid,
        ]);

        $this->assertnotEquals(null, $link);
    }

    public function testUnlinkRegionPlaylist(): void
    {
        $client = static::createClient();
        $utils = static::getContainer()->get('App\Utils\Utils');

        $iri = $this->findIriBy(Screen::class, []);
        $screenUlid = $utils->getUlidFromIRI($iri);

        $iri = $this->findIriBy(Playlist::class, []);
        $playlistUlid = $utils->getUlidFromIRI($iri);

        $iri = $this->findIriBy(ScreenLayoutRegions::class, []);
        $regionsUlid = $utils->getUlidFromIRI($iri);

        $url = '/v1/screens/'.$screenUlid.'/regions/'.$regionsUlid.'/playlists/'.$playlistUlid;

        // Ensure link exists and is created before deleting it.
        $client->request('PUT', $url, [
            'json' => [],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $link = static::getContainer()->get('doctrine')->getRepository(PlaylistScreenRegion::class)->findOneBy([
            'playlist' => $playlistUlid,
            'screen' => $screenUlid,
            'region' => $regionsUlid,
        ]);
        $this->assertnotEquals(null, $link);

        // Remove the link just created.
        $client->request('DELETE', $url, [
            'json' => [],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(204);

        $unlink = static::getContainer()->get('doctrine')->getRepository(PlaylistScreenRegion::class)->findOneBy([
            'playlist' => $playlistUlid,
            'screen' => $screenUlid,
            'region' => $regionsUlid,
        ]);
        $this->assertEquals(null, $unlink);
    }
}
