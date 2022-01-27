<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Playlist;
use App\Tests\BaseTestTrait;

class PlaylistsTest extends ApiTestCase
{
    use BaseTestTrait;

    public function testGetCollection(): void
    {
        $response = static::createClient()->request('GET', '/v1/playlists?itemsPerPage=5', ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Playlist',
            '@id' => '/v1/playlists',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 10,
            'hydra:view' => [
                '@id' => '/v1/playlists?itemsPerPage=5&page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/v1/playlists?itemsPerPage=5&page=1',
                'hydra:last' => '/v1/playlists?itemsPerPage=5&page=2',
                'hydra:next' => '/v1/playlists?itemsPerPage=5&page=2',
            ],
        ]);

        $this->assertCount(5, $response->toArray()['hydra:member']);

        // @TODO: published: Object value found, but an array is required
//        $this->assertMatchesResourceCollectionJsonSchema(Playlist::class, 'get-v1-screen-groups');
    }

    public function testGetItem(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Playlist::class, []);

        $client->request('GET', $iri, ['headers' => ['Content-Type' => 'application/ld+json']]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => [
                '@vocab' => 'http://example.com/docs.jsonld#',
                'hydra' => 'http://www.w3.org/ns/hydra/core#',
                'title' => 'Playlist/title',
                'description' => 'Playlist/description',
                'schedules' => 'Playlist/schedules',
                'created' => 'Playlist/created',
                'modified' => 'Playlist/modified',
                'modifiedBy' => 'Playlist/modifiedBy',
                'createdBy' => 'Playlist/createdBy',
                'slides' => 'Playlist/slides',
            ],
            '@type' => 'Playlist',
            '@id' => $iri,
        ]);
    }

    public function testCreatePlaylist(): void
    {
        $response = static::createClient()->request('POST', '/v1/playlists', [
            'json' => [
                'title' => 'Test playlist',
                'description' => 'This is a test playlist',
                'schedules' => [
                    [
                        'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                        'duration' => 1000,
                    ],
                    [
                        'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                        'duration' => 2000,
                    ],
                ],
                'modifiedBy' => 'Test Tester',
                'createdBy' => 'Hans Tester',
                'published' => [
                    'from' => '2021-09-21T17:00:01.000Z',
                    'to' => '2021-07-22T17:00:01.000Z',
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
                'title' => 'Playlist/title',
                'description' => 'Playlist/description',
                'schedules' => 'Playlist/schedules',
                'created' => 'Playlist/created',
                'modified' => 'Playlist/modified',
                'modifiedBy' => 'Playlist/modifiedBy',
                'createdBy' => 'Playlist/createdBy',
                'slides' => 'Playlist/slides',
            ],
            '@type' => 'Playlist',
            'title' => 'Test playlist',
            'description' => 'This is a test playlist',
            'schedules' => [
                [
                    'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                    'duration' => 1000,
                ],
                [
                    'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                    'duration' => 2000,
                ],
            ],
            'modifiedBy' => 'Test Tester',
            'createdBy' => 'Hans Tester',
            'published' => [
                'from' => '2021-09-21T17:00:01.000Z',
                'to' => '2021-07-22T17:00:01.000Z',
            ],
        ]);
        $this->assertMatchesRegularExpression('@^/v\d/\w+/([A-Za-z0-9]{26})$@', $response->toArray()['@id']);

        $response = static::createClient()->request('POST', '/v1/playlists', [
            'json' => [
                'title' => 'Test playlist',
                'description' => 'This is a test playlist',
                'schedules' => [
                    [
                        'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                        'duration' => 1000,
                    ],
                    [
                        'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                        'duration' => 2000,
                    ],
                ],
                'modifiedBy' => 'Test Tester',
                'createdBy' => 'Hans Tester',
                'published' => [
                    'from' => '2021-09-21T17:00:01.000Z',
                    'to' => '2021-07-22T17:00:01.000Z',
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
                'title' => 'Playlist/title',
                'description' => 'Playlist/description',
                'schedules' => 'Playlist/schedules',
                'created' => 'Playlist/created',
                'modified' => 'Playlist/modified',
                'modifiedBy' => 'Playlist/modifiedBy',
                'createdBy' => 'Playlist/createdBy',
                'slides' => 'Playlist/slides',
            ],
            '@type' => 'Playlist',
            'title' => 'Test playlist',
            'description' => 'This is a test playlist',
            'schedules' => [
                [
                    'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                    'duration' => 1000,
                ],
                [
                    'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                    'duration' => 2000,
                ],
            ],
            'modifiedBy' => 'Test Tester',
            'createdBy' => 'Hans Tester',
            'published' => [
                'from' => '2021-09-21T17:00:01.000Z',
                'to' => '2021-07-22T17:00:01.000Z',
            ],
        ]);
        $this->assertMatchesRegularExpression('@^/v\d/\w+/([A-Za-z0-9]{26})$@', $response->toArray()['@id']);

        // @TODO: published: Object value found, but an array is required
        // $this->assertMatchesResourceItemJsonSchema(Playlist::class);
    }

    public function testCreateUnpublishedPlaylist(): void
    {
        $response = static::createClient()->request('POST', '/v1/playlists', [
            'json' => [
                'title' => 'Test playlist',
                'description' => 'This is a test playlist',
                'schedules' => [
                    [
                        'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                        'duration' => 1000,
                    ],
                    [
                        'rrule' => 'DTSTART:20211102T232610Z\nRRULE:FREQ=MINUTELY;COUNT=11;INTERVAL=8',
                        'duration' => 2000,
                    ],
                ],
                'modifiedBy' => 'Test Tester',
                'createdBy' => 'Hans Tester',
                'published' => [
                    'from' => null,
                    'to' => null,
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@type' => 'Playlist',
            'title' => 'Test playlist',
            'description' => 'This is a test playlist',
            'published' => [
                'from' => null,
                'to' => null,
            ],
        ]);
    }

    public function testCreateInvalidPlaylist(): void
    {
        static::createClient()->request('POST', '/v1/playlists', [
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

    public function testCreateInvalidPlaylistTime(): void
    {
        static::createClient()->request('POST', '/v1/playlists', [
            'json' => [
                'published' => [
                    'from' => '2021-09-201T17:00:01.000Z',
                    'to' => '2021-42-22T17:00:01.000Z',
                ],
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
            'hydra:description' => '2021-09-201T17:00:01.000Z is not a valid date format, valid format is simplified extended ISO format, e.g 1970-01-01T01:02:03.000Z',
        ]);
    }

    public function testUpdatePlaylist(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Playlist::class, []);

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
            '@type' => 'Playlist',
            '@id' => $iri,
            'title' => 'Updated title',
        ]);
    }

    public function testUpdatePlaylistToUnpublished(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Playlist::class, []);

        $client->request('PUT', $iri, [
            'json' => [
                'title' => 'Updated title',
                'published' => [
                    'from' => null,
                    'to' => null,
                ],
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@type' => 'Playlist',
            '@id' => $iri,
            'title' => 'Updated title',
            'published' => [
                'from' => null,
                'to' => null,
            ],
        ]);
    }

    public function testDeletePlaylist(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Playlist::class, []);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);

        $ulid = $this->iriHelperUtils->getUlidFromIRI($iri);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Playlist::class)->findOneBy(['id' => $ulid])
        );
    }
}
