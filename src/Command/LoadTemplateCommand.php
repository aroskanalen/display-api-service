<?php

namespace App\Command;

use App\Entity\Template;
use Doctrine\ORM\EntityManagerInterface;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Ulid;

#[AsCommand(
    name: 'app:template:load',
    description: 'Load a template from a json file',
)]
class LoadTemplateCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'json file to load');
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($filename = $input->getArgument('filename')) {
            try {
                $content = json_decode(file_get_contents($filename), false, 512, JSON_THROW_ON_ERROR);

                // Validate template json.
                $schemaStorage = new SchemaStorage();
                $jsonSchemaObject = $this->getSchema();
                $schemaStorage->addSchema('file://contentSchema', $jsonSchemaObject);
                $validator = new Validator(new Factory($schemaStorage));
                $validator->validate($content, $jsonSchemaObject);

                if ($validator->isValid()) {
                    $io->info('The supplied JSON validates against the schema.');
                } else {
                    $message = "JSON does not validate. Violations:\n";
                    foreach ($validator->getErrors() as $error) {
                        $message = $message.sprintf("\n[%s] %s", $error['property'], $error['message']);
                    }

                    $io->error($message);

                    return Command::INVALID;
                }

                $loadedTemplate = $this->entityManager->getRepository(Template::class)->findBy(['id' => Ulid::fromString($content->id)]);

                if (is_array($loadedTemplate) & 0 === count($loadedTemplate)) {
                    // If the template doesnt exist, a new will be created
                    $template = new Template();
                    $template->setId(Ulid::fromString($content->id));
                } else {
                    // If the template already exists it will be replaced
                    $template = array_shift($loadedTemplate);
                }

                $template->setIcon($content->icon);
                // @TODO: Resource should be an object.
                $template->setResources(get_object_vars($content->resources));
                $template->setTitle($content->title);
                $template->setDescription($content->description);

                $this->entityManager->persist($template);
                $this->entityManager->flush();

                $id = $template->getId();
                $io->success("Template added with id: ${id}");

                return Command::SUCCESS;
            } catch (\JsonException $exception) {
                $io->error('Invalid json');

                return Command::INVALID;
            }
        } else {
            $io->error('No filename specified.');

            return Command::INVALID;
        }
    }

    /**
     * Supplies json schema for validation.
     *
     * @return mixed
     *   Json schema
     *
     * @throws \JsonException
     */
    private function getSchema(): mixed
    {
        $jsonSchema = <<<'JSON'
        {
          "$schema": "https://json-schema.org/draft/2020-12/schema",
          "$id": "https://os2display.dk/config-schema.json",
          "title": "Config file schema",
          "description": "Schema for defining config files for templates",
          "type": "object",
          "properties": {
            "icon": {
              "description": "An icon for the template",
              "type": "string"
            },
            "description": {
              "description": "A description of the template",
              "type": "string"
            },
            "resources": {
              "type": "object",
              "properties": {
                "schema": {
                  "description": "Path to the json schema for the content",
                  "type": "string"
                },
                "component": {
                  "description": "Path to the react remote component that renders the content",
                  "type": "string"
                },
                "admin": {
                  "description": "Path to the json array describing the content form in the administration interface",
                  "type": "string"
                },
                "assets": {
                  "description": "Assets that are needed by the template",
                  "type": "array",
                  "items": {
                    "type": "object",
                    "description": "Asset item",
                    "properties": {
                      "type": {
                        "type": "string",
                        "url": "string"
                      }
                    }
                  }
                },
                "options": {
                  "description": "Default option values for the template",
                  "type": "object"
                },
                "content": {
                  "description": "Default content values for the template",
                  "type": "object"
                }
              },
              "required": ["schema", "component", "admin", "assets", "options", "content"]
            },
            "title": {
              "description": "The title of the template",
              "type": "string"
            }
          },
          "required": ["icon", "description", "resources", "title"]
        }
        JSON;

        return json_decode($jsonSchema, false, 512, JSON_THROW_ON_ERROR);
    }
}
