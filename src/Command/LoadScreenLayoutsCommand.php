<?php

namespace App\Command;

use App\Entity\Tenant;
use App\Entity\Tenant\ScreenLayout;
use App\Entity\Tenant\ScreenLayoutRegions;
use App\Repository\ScreenLayoutRepository;
use App\Repository\TenantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Ulid;

#[AsCommand(
    name: 'app:screen-layouts:load',
    description: 'Load a set of predefined screen layouts',
)]
class LoadScreenLayoutsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TenantRepository $tenantRepository,
        private ScreenLayoutRepository $screenLayoutRepository,
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

        $tenants = $this->tenantRepository->findAll();

        $question = new Question('Which tenant should the layout be added to?');
        $question->setAutocompleterValues(array_reduce($tenants, function (array $carry, Tenant $tenant) {
            $carry[$tenant->getTenantKey()] = $tenant->getTenantKey();

            return $carry;
        }, []));
        $tenantSelected = $io->askQuestion($question);

        if (empty($tenantSelected)) {
            $io->error('No tenant selected. Aborting.');

            return Command::INVALID;
        }

        $tenant = $this->tenantRepository->findOneBy(['tenantKey' => $tenantSelected]);

        if ($tenant == null) {
            $io->error('Tenant not found.');

            return Command::INVALID;
        }

        $io->info("Screen layout will be added to $tenantSelected tenant.");

        try {
            $filename = $input->getArgument('filename');
            $content = json_decode(file_get_contents($filename), false, 512, JSON_THROW_ON_ERROR);

            if (isset($content->id) && Ulid::isValid($content->id)) {
                $screenLayout = $this->screenLayoutRepository->findOneBy(['id' => Ulid::fromString($content->id)]);

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

            $screenLayout->setTenant($tenant);
            $screenLayout->setTitle($content->title);
            $screenLayout->setGridColumns($content->grid->columns);
            $screenLayout->setGridRows($content->grid->rows);

            foreach ($content->regions as $localRegion) {
                $region = new ScreenLayoutRegions();
                $region->setTenant($tenant);
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
