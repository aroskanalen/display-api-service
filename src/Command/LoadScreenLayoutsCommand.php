<?php

namespace App\Command;

use App\Entity\Tenant\ScreenLayout;
use App\Entity\Tenant\ScreenLayoutRegions;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
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
        $this->addArgument('filename', InputArgument::REQUIRED, 'json file to load. Can be a local file or a URL');
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $successMessage = 'Screen layout updated';

        try {
            $filename = $input->getArgument('filename');
            $content = json_decode(file_get_contents($filename), false, 512, JSON_THROW_ON_ERROR);

            if (isset($content->id) && Ulid::isValid($content->id)) {
                $repository = $this->entityManager->getRepository(ScreenLayout::class);
                $screenLayout = $repository->findOneBy(['id' => Ulid::fromString($content->id)]);

                if (!$screenLayout) {
                    $screenLayout = new ScreenLayout();
                    $metadata = $this->entityManager->getClassMetaData(get_class($screenLayout));
                    $metadata->setIdGenerator(new AssignedGenerator());

                    $ulid = Ulid::fromString($content->id);

                    $screenLayout->setId($ulid);

                    $this->entityManager->persist($screenLayout);
                    $successMessage = 'Screen layout added';
                }
            } else {
                $io->error('The screen layout should have an id (ulid)');

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

            $this->entityManager->flush();

            $io->success($successMessage);

            return Command::SUCCESS;
        } catch (\JsonException $exception) {
            $io->error('Invalid json');

            return Command::INVALID;
        }
    }
}
