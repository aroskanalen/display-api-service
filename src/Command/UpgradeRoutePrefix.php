<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Tenant;
use App\Entity\Tenant\FeedSource;
use App\Repository\FeedSourceRepository;
use App\Repository\SlideRepository;
use App\Repository\TenantRepository;
use App\Service\FeedService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:upgrade-route-prefix',
    description: 'Upgrades route prefix',
)]
class UpgradeRoutePrefix extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SlideRepository $slideRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('old_prefix', null,InputOption::VALUE_REQUIRED, 'Old route prefix. E.g. v1');
        $this->addOption('new_prefix', null,InputOption::VALUE_REQUIRED, 'New route prefix. E.g. v2');
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $oldPrefix = $input->getOption('old_prefix');
        if (is_null($oldPrefix)) {
            $io->error("--old_prefix=[PREFIX] is required. E.g. v1");
            return Command::FAILURE;
        }

        $newPrefix = $input->getOption('new_prefix');
        if (is_null($newPrefix)) {
            $io->error("--new_prefix=[PREFIX] is required. E.g. v2");
            return Command::FAILURE;
        }

        $slides = $this->slideRepository->findAll();

        foreach ($slides as $slide) {
            $contentAsString = json_encode($slide->getContent());

            $newContentAsString = str_replace("\/".$oldPrefix."\/media\/", "\/".$newPrefix."\/media\/", $contentAsString);

            $slide->setContent(json_decode($newContentAsString, null, 512, JSON_OBJECT_AS_ARRAY|JSON_THROW_ON_ERROR));
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
