<?php

namespace App\Feed;

use App\Entity\Tenant\Feed;
use App\Entity\Tenant\FeedSource;
use App\Exceptions\MissingFeedConfigurationException;
use App\Service\FeedService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KobaFeedType implements FeedTypeInterface
{
    public const SUPPORTED_FEED_TYPE = 'calendar';

    public function __construct(
        private FeedService $feedService,
        private HttpClientInterface $client,
        private LoggerInterface $logger
    ) {}

    /**
     * @param Feed $feed
     *
     * @return array
     *
     * @throws MissingFeedConfigurationException
     */
    public function getData(Feed $feed): array
    {
        $results = [];

        $feedSource = $feed->getFeedSource();
        $secrets = $feedSource?->getSecrets();
        $configuration = $feed->getConfiguration();

        if (!isset($secrets['kobaHost']) || !isset($secrets['kobaApiKey'])) {
            $this->logger->error('KobaFeedType: "Host" and "ApiKey" not configured.');

            throw new MissingFeedConfigurationException('Koba feed "Host" and "ApiKey" not configured');
        }

        $kobaHost = $secrets['kobaHost'];
        $kobaApiKey = $secrets['kobaApiKey'];
        $kobaGroup = $secrets['kobaGroup'] ?? 'default';
        $filterList = $configuration['filterList'] ?? false;
        $rewriteBookedTitles = $configuration['rewriteBookedTitles'] ?? false;

        if (!isset($configuration['resources'])) {
            $this->logger->error('KobaFeedType: Resources not set.');

            throw new MissingFeedConfigurationException('resources not configured');
        }

        $resources = $configuration['resources'];

        // Round down to the nearest hour.
        $from = time() - (time() % 3600);

        // Get bookings for the coming week.
        // @TODO: Support for configuring interest period.
        $to = $from + 7 * 24 * 60 * 60;

        foreach ($resources as $resource) {
            try {
                $bookings = $this->getBookingsFromResource($kobaHost, $kobaApiKey, $resource, $kobaGroup, $from, $to);
            } catch (Exception) {
                $this->logger->error('KobaFeedType: Get bookings from resources failed.');
                continue;
            }

            foreach ($bookings as $booking) {
                $title = $booking['event_name'] ?? '';

                // Apply list filter. If enabled it removes all events that do not have (liste) in title.
                if ($filterList) {
                    if (!str_contains($title, '(liste)')) {
                        continue;
                    } else {
                        $title = str_replace('(liste)', '', $title);
                    }
                }

                // Apply booked title override. If enabled it changes the title to Optaget if it contains (optaget).
                if ($rewriteBookedTitles) {
                    if (str_contains($title, '(optaget)')) {
                        $title = 'Optaget';
                    }
                }

                $results[] = [
                    'id' => Ulid::generate(),
                    'title' => $title,
                    'description' => $booking['event_description'] ?? '',
                    'startTime' => $booking['start_time'] ?? '',
                    'endTime' => $booking['end_time'] ?? '',
                    'resourceTitle' => $booking['resource_alias'] ?? '',
                    'resourceId' => $booking['resource_id'] ?? '',
                ];
            }
        }

        // Sort bookings by start time.
        usort($results, function ($a, $b) {
            return strcmp($a['startTime'], $b['startTime']);
        });

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function getAdminFormOptions(FeedSource $feedSource): array
    {
        $endpoint = $this->feedService->getFeedSourceConfigUrl($feedSource, 'resources');

        // @TODO: Translation.
        return [
            [
                'key' => 'koba-resource-selector',
                'input' => 'multiselect-from-endpoint',
                'endpoint' => $endpoint,
                'name' => 'resources',
                'label' => 'Vælg resurser',
                'helpText' => 'Her vælger du hvilke resourcer der skal hentes indgange fra.',
                'formGroupClasses' => 'col-md-6 mb-3',
            ],
            [
                'key' => 'koba-resource-rewrite-booked',
                'input' => 'checkbox',
                'name' => 'rewriteBookedTitles',
                'label' => 'Omskriv titler med (optaget)',
                'helpText' => 'Denne mulighed gør at titler som indeholder (optaget) bliver omskrevet til "Optaget".',
                'formGroupClasses' => 'col mb-3',
            ],
            [
                'key' => 'koba-resource-filter-not-list',
                'input' => 'checkbox',
                'name' => 'filterList',
                'label' => 'Vis kun begivenheder med (liste) i titlen',
                'helpText' => 'Denne mulighed fjerner begivenheder der IKKE har (liste) i titlen. Den fjerner også (liste) fra titlen.',
                'formGroupClasses' => 'col mb-3',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getConfigOptions(Request $request, FeedSource $feedSource, string $name): ?array
    {
        if ('resources' === $name) {
            $secrets = $feedSource->getSecrets();

            if (!isset($secrets['kobaHost']) || !isset($secrets['kobaApiKey'])) {
                return [];
            }

            $kobaHost = $secrets['kobaHost'];
            $kobaApiKey = $secrets['kobaApiKey'];
            $kobaGroup = $secrets['kobaGroup'] ?? 'default';

            $requestUrl = "$kobaHost/api/resources/group/$kobaGroup";

            $response = $this->client->request('GET', $requestUrl, [
                'query' => [
                    'apikey' => $kobaApiKey,
                ],
            ]);

            $content = $response->toArray();

            $resources = [];

            foreach ($content as $entry) {
                // Ignore entries without mail.
                if (empty($entry['mail'])) {
                    continue;
                }

                $mail = $entry['mail'];
                $alias = !empty($entry['alias']) ? " (${entry['alias']})" : '';
                $name = $entry['name'] ?? $mail;

                // Make sure a title has been set.
                $title = $name.$alias;

                $resources[] = [
                    'id' => Ulid::generate(),
                    'title' => $title,
                    'value' => $entry['mail'],
                ];
            }

            usort($resources, function ($a, $b) {
                return strcmp($a['title'], $b['title']);
            });

            return $resources;
        }

        return null;
    }

    public function getRequiredSecrets(): array
    {
        return ['kobaHost', 'kobaApiKey'];
    }

    public function getRequiredConfiguration(): array
    {
        return ['resources'];
    }

    public function getSupportedFeedOutputType(): string
    {
        return self::SUPPORTED_FEED_TYPE;
    }

    /**
     * @param string $host
     * @param string $apikey
     * @param string $resource
     * @param string $group
     * @param int $from
     * @param int $to
     *
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getBookingsFromResource(string $host, string $apikey, string $resource, string $group, int $from, int $to): array
    {
        try {
            $requestUrl = "$host/api/resources/$resource/group/$group/bookings/from/$from/to/$to";

            $response = $this->client->request('GET', $requestUrl, [
                'query' => [
                    'apikey' => $apikey,
                ],
            ]);

            return $response->toArray();
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            $this->logger->error('Error building koba data. CODE: '.$e->getCode().', MESSAGE: '.$e->getMessage());

            throw $e;
        }
    }
}
