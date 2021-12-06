<?php

namespace App\Command;

use App\Entity\ScreenLayout;
use App\Entity\ScreenLayoutRegions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Ulid;

#[AsCommand(
    name: 'app:screen-layouts:load',
    description: 'Load a set of predefined screen layouts',
)]
class LoadScreenLayoutsCommand extends Command
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

                if (isset($content->id) & Ulid::isValid($content->id)) {
                    $loadedScreenLayout = $this->entityManager->getRepository(ScreenLayout::class)->findOneBy(['id' => Ulid::fromString($content->id)]);

                    if (is_null($loadedScreenLayout)) {
                        // If the screen layout doesnt exist, a new will be created
                        $screenLayout = new ScreenLayout();
                        $screenLayout->setId(Ulid::fromString($content->id));
                    } else {
                        // If the screen layout already exists it will be replaced
                        $screenLayout = $loadedScreenLayout;
                    }
                } else {
                    $io->error('The screen should have an id (ulid)');

                    return Command::INVALID;
                }

                $screenLayout->setTitle($content->title);
                $screenLayout->setGridColumns($content->grid->columns);
                $screenLayout->setGridRows($content->grid->rows);

                foreach ($content->regions as $localRegion) {
                    $region = new ScreenLayoutRegions();
                    $region->setGridArea($localRegion->gridArea);
                    $region->setTitle($localRegion->title);
                    $this->entityManager->persist($region);
                    $screenLayout->addRegion($region);
                }

                $this->entityManager->persist($screenLayout);
                $this->entityManager->flush();

                $io->success('Screen layout added');

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
}
