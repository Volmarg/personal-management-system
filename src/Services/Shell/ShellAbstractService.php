<?php

namespace App\Services\Shell;

use App\Services\Core\Logger;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Main / Common logic of the shell executable logic
 */
abstract class ShellAbstractService
{
    /**
     * @param Logger             $loggerService
     * @param ContainerInterface $container
     */
    public function __construct(
        private readonly Logger             $loggerService,
        private readonly ContainerInterface $container
    ) {
    }

    /**
     * Will retrieve executable binary used in child class
     */
    abstract protected function getExecutableBinaryName(): string;

    /**
     * Will return information if executable is present (calls `which`).
     *
     * @return bool
     * @throws Exception
     */
    protected function isExecutableForServicePresent(): bool
    {
        $binaryName = $this->getExecutableBinaryName();
        return $this->isExecutablePresent($binaryName);
    }

    /**
     * Will take the partials and attach each one of them to the executable binary like this:
     *  - assuming executable: "Mysql"
     *  - partials: [1, --2=test, 3]
     *
     * Will result in:
     * - "Mysql 1 --2=test 3
     *
     * @param array<string|int> $partials
     * @param bool              $addSpaceBarPerPartial
     * @param int               $timeout
     *
     * @return string
     */
    protected function buildCommand(array $partials = [], bool $addSpaceBarPerPartial = true, int $timeout = 0): string
    {
        $gluedCommand = "";
        if ($timeout > 0) {
            $gluedCommand .= "timeout {$timeout} ";
        }

        $gluedCommand .= $this->getExecutableBinaryName();
        foreach ($partials as $partial) {

            if ($addSpaceBarPerPartial) {
                $gluedCommand .= " " . $partial;
                continue;
            }

            $gluedCommand .= $partial;
        }

        return $gluedCommand;
    }

    /**
     * Will check if executable is present
     *
     * @param string $executableName
     * @return bool
     * @throws Exception
     */
    protected function isExecutablePresent(string $executableName): bool
    {
        $executableFinder = new ExecutableFinder();
        $executablePath   = $executableFinder->find($executableName);

        if (is_null($executablePath)) {
            $this->loggerService->getLogger()->critical("Searched executable is not present for shell: {$executableName}");
            return false;
        }

        return true;
    }

    /**
     * Execute shell command and return the process object
     *
     * @param string         $calledCommand
     * @param int|float|null $timeout
     *
     * @return Process
     */
    protected function executeShellCommand(string $calledCommand, null|int|float $timeout = null): Process
    {
        $process = Process::fromShellCommandline(trim($calledCommand));

        if (!empty($timeout)) {
            $process->setTimeout((float)$timeout);
        }

        $process->run();

        if (!$process->isSuccessful()) {
            $loggedCommand = $calledCommand;
            $this->loggerService->getLogger()->critical("Process was finished but WITH NO SUCCESS", [
                "calledCommand"      => $loggedCommand,
                "commandOutput"      => $process->getOutput(),
                "commandErrorOutput" => $process->getErrorOutput(),
            ]);
        }

        return $process;
    }

}