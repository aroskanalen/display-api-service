<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\SlideInput;
use App\Entity\Slide;
use App\Repository\TemplateRepository;
use App\Utils\Utils;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SlideInputDataTransformer implements DataTransformerInterface
{
    private Utils $utils;
    private TemplateRepository $templateRepository;

    public function __construct(Utils $utils, TemplateRepository $templateRepository)
    {
        $this->utils = $utils;
        $this->templateRepository = $templateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data, string $to, array $context = []): Slide
    {
        $slide = new Slide();
        if (array_key_exists(AbstractItemNormalizer::OBJECT_TO_POPULATE, $context)) {
            $slide = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];
        }

        /* @var SlideInput $data */
        empty($data->title) ?: $slide->setTitle($data->title);
        empty($data->description) ?: $slide->setDescription($data->description);
        empty($data->createdBy) ?: $slide->setCreatedBy($data->createdBy);
        empty($data->modifiedBy) ?: $slide->setModifiedBy($data->modifiedBy);
        empty($data->duration) ?: $slide->setDuration($data->duration);
        empty($data->published['from']) ?: $slide->setPublishedFrom($this->utils->validateDate($data->published['from']));
        empty($data->published['to']) ?: $slide->setPublishedTo($this->utils->validateDate($data->published['to']));
        empty($data->templateInfo['options']) ?: $slide->setTemplateOptions($data->templateInfo['options']);
        empty($data->content) ?: $slide->setContent($data->content);

        // @TODO: Should the regex below contain path and should it be hardcoded.
        if (!empty($data->templateInfo['@id'])) {
            // Validate that layout exists path.
            preg_match('@^/v1/templates/([A-Za-z0-9]{26})$@', $data->templateInfo['@id'], $matches);
            if (2 !== count($matches)) {
                throw new InvalidArgumentException('Unknown template resource');
            }

            // Try loading layout entity.
            $template = $this->templateRepository->findOneBy(['id' => end($matches)]);
            if (is_null($template)) {
                throw new InvalidArgumentException('Unknown template resource');
            }

            $slide->setTemplate($template);
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
