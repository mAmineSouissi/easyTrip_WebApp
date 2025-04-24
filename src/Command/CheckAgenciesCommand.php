<?php

namespace App\Command;

use App\Repository\AgencyRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-agencies',
    description: 'Vérifie les agences dans la base de données',
)]
class CheckAgenciesCommand extends Command
{
    public function __construct(
        private AgencyRepository $agencyRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $agencies = $this->agencyRepository->findAll();
        
        if (empty($agencies)) {
            $io->error('Aucune agence trouvée dans la base de données !');
            return Command::SUCCESS;
        }

        $io->success(sprintf('Nombre total d\'agences : %d', count($agencies)));
        
        $table = [];
        foreach ($agencies as $agency) {
            $table[] = [
                $agency->getId(),
                $agency->getName(),
                $agency->getEmail(),
                filter_var($agency->getEmail(), FILTER_VALIDATE_EMAIL) ? 'Valide' : 'Non valide'
            ];
        }
        
        $io->table(
            ['ID', 'Nom', 'Email', 'Statut Email'],
            $table
        );

        return Command::SUCCESS;
    }
} 