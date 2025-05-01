<?php

namespace App\Command\Security;

use App\Repository\UserRepository;
use App\Services\Security\JwtAuthenticationService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TypeError;

/**
 * Will generate the internal jwt token
 */
class GenerateInternalJwtTokenCommand extends Command
{
    const COMMAND_NAME = "pms:security:jwt:generate-internal-token";

    private const OPTION_USER_EMAIL = "user-email";

    private const OPTION_NON_EXPIRING = "non-expiring";

    /**
     * Set configuration
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->addOption(self::OPTION_USER_EMAIL, null, InputOption::VALUE_REQUIRED, "User-email for which token will be generated");
        $this->setDescription("Will generate the jwt token used to access the project");
        $this->addOption(self::OPTION_NON_EXPIRING, null, InputOption::VALUE_NONE, "This token will never expire, at least not in Your lifetime");
    }

    public function __construct(
        private readonly JwtAuthenticationService $jwtTokenService,
        private readonly UserRepository           $userRepository,
    )
    {
        parent::__construct();
    }

    /**
     * Execute command logic
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $nonExpiring = $input->hasOption(self::OPTION_NON_EXPIRING);
            $userEmail   = $input->getOption(self::OPTION_USER_EMAIL);
            if (empty($userEmail)) {
                $io->error("User email is missing");
                return self::INVALID;
            }

            $user = $this->userRepository->findOneByEmail($userEmail);
            if(empty($user)){
                $io->error("User for given email does not exist: {$userEmail}");
                return self::INVALID;
            }

            $token = $this->jwtTokenService->buildTokenForUser($user, [], $nonExpiring);

            $io->info("Jwt token");
            $io->text($token);
            $io->newLine(2);
        }catch(Exception | TypeError $e){
            $io->error($e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

}