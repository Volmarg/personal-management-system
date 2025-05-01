<?php

namespace App\Command\Security;

use App\Services\Security\JwtAuthenticationService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TypeError;

/**
 * Helper command to extract the jwt token payload
 *
 * Class ExtractJwtTokenPayloadCommand
 */
class ExtractJwtTokenPayloadCommand extends Command
{
    private const COMMAND_NAME = "pms:security:extract-jwt-token-payload";
    private const MAX_PAYLOAD_CHUNK_SIZE = 7;

    /**
     * Set configuration
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription("Will extract jwt token payload");
    }

    public function __construct(
        private readonly JwtAuthenticationService $jwtAuthenticationService
    ) {
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
        $io    = new SymfonyStyle($input, $output);
        $token = $io->ask("Jwt token: ");

        try {
            $isTokenValid = $this->jwtAuthenticationService->isTokenValid($token);
            if (!$isTokenValid) {
                $io->error("This is not a valid JWT token!");

                return self::FAILURE;
            }

            $payload = $this->jwtAuthenticationService->getPayloadFromToken($token);
            $chunks  = array_chunk($payload, self::MAX_PAYLOAD_CHUNK_SIZE, true);
            foreach ($chunks as $payloadChunk) {
                $this->showTableForChunk($payloadChunk, $io);
            }
        } catch (Exception|TypeError $e) {
            $io->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Splits the payload result into multiple tables because the more data there is
     * the harder it gets to read the cli table
     *
     * @param array        $payloadChunk
     * @param SymfonyStyle $io
     */
    private function showTableForChunk(array $payloadChunk, SymfonyStyle $io): void
    {
        $tableHeaders = array_keys($payloadChunk);
        $tableRows    = [];
        foreach ($payloadChunk as $key => $value) {

            if (is_scalar($value)) {
                $tableRows[] = $value;
            } else {
                $encodedValue = json_encode($value);
                $tableRows[]  = $encodedValue;
            }
        }

        $io->table($tableHeaders, [$tableRows]);
    }

}