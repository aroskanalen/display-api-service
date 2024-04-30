<?php

declare(strict_types=1);

namespace App\Feed;

use App\Entity\Tenant\Feed;
use App\Entity\Tenant\FeedSource;
use App\Service\FeedService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see https://api.listen.notified.com/docs/index.html
 */
class NotifiedFeedType implements FeedTypeInterface
{
    final public const SUPPORTED_FEED_TYPE = 'instagram';
    final public const REQUEST_TIMEOUT = 10;

    private const BASE_URL = 'https://api.listen.notified.com';

    public function __construct(
        private readonly FeedService $feedService,
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {}

    public function getData(Feed $feed): array
    {
        try {
            $secrets = $feed->getFeedSource()?->getSecrets();
            if (!isset($secrets['token'])) {
                return [];
            }

            $configuration = $feed->getConfiguration();
            if (!isset($configuration['feeds']) || 0 === count($configuration['feeds'])) {
                return [];
            }

            $slide = $feed->getSlide();
            $slideContent = $slide->getContent();

            $pageSize = $slideContent['maxEntries'] ?? 10;

            $token = $secrets['token'];

            $body = [
                "pageSize" => $pageSize,
                "page" => 1,
                "searchProfileIds" => $configuration['feeds'],
            ];

            $res = $this->client->request(
                'POST',
                self::BASE_URL.'/api/listen/mentions',
                [
                    'timeout' => self::REQUEST_TIMEOUT,
                    'headers' => [
                        "Accept" => 'application/json',
                        "Content-Type" => 'application/json',
                        'Notified-Custom-Token' => $token,
                    ],
                    'body' => json_encode($body),
                ]
            );

            $contents = $res->getContent();
            $data = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);

            return array_map(fn (object $item) => $this->getFeedItemObject($item), $data);
        } catch (\Throwable $throwable) {
            $this->logger->error('{code}: {message}', [
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAdminFormOptions(FeedSource $feedSource): array
    {
        $endpoint = $this->feedService->getFeedSourceConfigUrl($feedSource, 'feeds');

        // @TODO: Translation.
        return [
            [
                'key' => 'notified-selector',
                'input' => 'multiselect-from-endpoint',
                'endpoint' => $endpoint,
                'name' => 'feeds',
                'label' => 'Vælg feed',
                'helpText' => 'Her vælger du hvilket feed der skal hentes indgange fra.',
                'formGroupClasses' => 'col-md-6 mb-3',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigOptions(Request $request, FeedSource $feedSource, string $name): ?array
    {
        try {
            if ('feeds' === $name) {
                $secrets = $feedSource->getSecrets();

                if (!isset($secrets['token'])) {
                    return [];
                }

                $token = $secrets['token'];

                $response = $this->client->request(
                    'GET',
                    self::BASE_URL.'/api/listen/searchprofiles',
                    [
                        'timeout' => self::REQUEST_TIMEOUT,
                        'headers' => [
                            "Accept" => 'application/json',
                            "Content-Type" => 'application/json',
                            'Notified-Custom-Token' => $token,
                        ],
                    ]
                );

                $contents = $response->getContent();

                $items = json_decode($contents, null, 512, JSON_THROW_ON_ERROR);

                return array_map(fn (object $item) => [
                    'id' => Ulid::generate(),
                    'title' => $item->name ?? '',
                    'value' => $item->id ?? '',
                ], $items);
            }
        } catch (\Throwable $throwable) {
            $this->logger->error('{code}: {message}', [
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredSecrets(): array
    {
        return ['token'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredConfiguration(): array
    {
        return ['feeds'];
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedFeedOutputType(): string
    {
        return self::SUPPORTED_FEED_TYPE;
    }

    /**
     * Parse feed item into object.
     */
    private function getFeedItemObject(object $item): object
    {
        return (object) [
            'text' => $item->description ?? null,
            'textMarkup' => null !== $item->description ? $this->wrapTags($item->description) : null,
            'mediaUrl' => $item->mediaUrl ?? null,
            // Video is not supported by the Notified Listen API.
            'videoUrl' => null,
            'username' => $item->sourceName ?? null,
            'createdTime' => $item->published ?? null,
        ];
    }

    private function wrapTags(string $input): string
    {
        $text = trim($input);

        // Strip unicode zero-width-space.
        $text = str_replace("\xE2\x80\x8B", '', $text);

        // Collects trailing tags one by one.
        $trailingTags = [];
        $pattern = "/\s*#(?<tag>[^\s#]+)\n?$/u";
        while (preg_match($pattern, (string) $text, $matches)) {
            // We're getting tags in reverse order.
            array_unshift($trailingTags, $matches['tag']);
            $text = preg_replace($pattern, '', (string) $text);
        }

        // Wrap sections in p tags.
        $text = preg_replace("/(.+)\n?/u", '<p>\1</p>', (string) $text);

        // Wrap inline tags.
        $pattern = '/(#(?<tag>[^\s#]+))/';

        return implode('', [
            '<div class="text">',
            preg_replace($pattern, '<span class="tag">\1</span>', (string) $text),
            '</div>',
            // Append tags.
            PHP_EOL,
            '<div class="tags">',
            implode(' ',
                array_map(fn ($tag) => '<span class="tag">#'.$tag.'</span>', $trailingTags)
            ),
            '</div>'
        ]);
    }
}
