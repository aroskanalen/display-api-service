<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use App\Dto\SlideInput;
use App\Entity\Tenant\Feed;
use App\Entity\Tenant\Slide;
use App\Repository\FeedRepository;
use App\Repository\FeedSourceRepository;
use App\Repository\MediaRepository;
use App\Repository\TemplateRepository;
use App\Repository\ThemeRepository;
use App\Utils\IriHelperUtils;
use App\Utils\ValidationUtils;

final class SlideInputDataTransformer implements DataTransformerInterface
{
    public function __construct(
        private ValidationUtils $utils,
        private IriHelperUtils $iriHelperUtils,
        private TemplateRepository $templateRepository,
        private ThemeRepository $themeRepository,
        private MediaRepository $mediaRepository,
        private FeedRepository $feedRepository,
        private FeedSourceRepository $feedSourceRepository,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function transform($object, string $to, array $context = []): Slide
    {
        $slide = new Slide();
        if (array_key_exists(AbstractItemNormalizer::OBJECT_TO_POPULATE, $context)) {
            $slide = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];
        }

        /* @var SlideInput $object */
        empty($object->title) ?: $slide->setTitle($object->title);
        empty($object->description) ?: $slide->setDescription($object->description);
        empty($object->createdBy) ?: $slide->setCreatedBy($object->createdBy);
        empty($object->modifiedBy) ?: $slide->setModifiedBy($object->modifiedBy);
        empty($object->duration) ?: $slide->setDuration($object->duration);

        if (null === $object->published['from']) {
            $slide->setPublishedFrom(null);
        } elseif (!empty($object->published['from'])) {
            $slide->setPublishedFrom($this->utils->validateDate($object->published['from']));
        }

        if (null === $object->published['to']) {
            $slide->setPublishedTo(null);
        } elseif (!empty($object->published['to'])) {
            $slide->setPublishedTo($this->utils->validateDate($object->published['to']));
        }

        empty($object->templateInfo['options']) ?: $slide->setTemplateOptions($object->templateInfo['options']);
        empty($object->content) ?: $slide->setContent($object->content);

        if (!empty($object->templateInfo['@id'])) {
            // Validate that template IRI exists.
            $ulid = $this->iriHelperUtils->getUlidFromIRI($object->templateInfo['@id']);

            // Try loading layout entity.
            $template = $this->templateRepository->findOneBy(['id' => $ulid]);
            if (is_null($template)) {
                throw new InvalidArgumentException('Unknown template resource');
            }

            $slide->setTemplate($template);
        }

        if (!empty($object->theme)) {
            // Validate that theme IRI exists.
            $ulid = $this->iriHelperUtils->getUlidFromIRI($object->theme);

            // Try loading theme entity.
            $theme = $this->themeRepository->findOneBy(['id' => $ulid]);
            if (is_null($theme)) {
                throw new InvalidArgumentException('Unknown theme resource');
            }

            $slide->setTheme($theme);
        }

        $slide->removeAllMedium();
        foreach ($object->media as $mediaIri) {
            // Validate that template IRI exists.
            $ulid = $this->iriHelperUtils->getUlidFromIRI($mediaIri);

            // Try loading media entity.
            $media = $this->mediaRepository->findOneBy(['id' => $ulid]);
            if (is_null($media)) {
                throw new InvalidArgumentException('Unknown media resource');
            }

            $slide->addMedium($media);
        }

        if (!empty($object->feed)) {
            $feedData = $object->feed;

            $feed = null;

            if (!empty($feedData['@id'])) {
                $feed = $this->feedRepository->find($feedData['@id']);
            }

            if (!$feed) {
                $feed = new Feed();
                $slide->setFeed($feed);
            }

            if (!empty($feedData['feedSource'])) {
                $feedUlid = $this->iriHelperUtils->getUlidFromIRI($feedData['feedSource']);
                $feedSource = $this->feedSourceRepository->find($feedUlid);

                if (is_null($feedSource)) {
                    throw new InvalidArgumentException('Unknown feedSource resource');
                }

                $feed->setFeedSource($feedSource);
            }

            empty($feedData['configuration']) ?: $feed->setConfiguration($feedData['configuration']);
        }

        return $slide;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof Slide) {
            return false;
        }

        return Slide::class === $to && null !== ($context['input']['class'] ?? null);
    }
}
