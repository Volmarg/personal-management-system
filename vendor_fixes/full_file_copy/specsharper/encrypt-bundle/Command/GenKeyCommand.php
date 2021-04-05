<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 06/05/17
 * Time: 13:41
 */

// src/AppBundle/Command/CreateUserCommand.php
namespace SpecShaper\EncryptBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class GenKeyCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('encrypt:genkey')

            // the short description shown while running "php bin/console list"
            ->setDescription('Generate a 256-bit encryption key.')
        ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $encryption_key_256bit = base64_encode(openssl_random_pseudo_bytes(32));

        $io = new SymfonyStyle($input, $output);

        $io->title('Generated Key');
        $io->success($encryption_key_256bit);

        return Command::SUCCESS;
    }
}