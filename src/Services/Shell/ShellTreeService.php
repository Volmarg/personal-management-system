<?php

namespace App\Services\Shell;

use Exception;
use LogicException;

/**
 * Relies on: {@link https://launchpad.net/ubuntu/+source/tree},
 */
class ShellTreeService extends ShellAbstractService
{
    const EXECUTABLE_BINARY_NAME = "tree";

    /**
     * Turn on JSON output. Outputs the directory tree as a JSON formatted array.
     */
    private const string ARG_JSON = "-J";

    /**
     * Print the size of each file (and dir) in bytes along with the name.
     */
    private const string ARG_GET_SIZE_BYTES = '-s';

    /**
     * Prints the full path prefix for each file.
     */
    private const string ARG_GET_ABSOLUTE_PATHS = '-f';

    /**
     * All files are printed. By default tree does not print hidden files (those beginning with a dot `.').
     * In no event does tree print the file system constructs `.' (current directory) and `..' (previous directory).
     */
    private const string ARG_GET_HIDDEN_FILES = '-a';

    /**
     * Omits printing of the file and directory report at the end of the tree listing.
     */
    private const string OPTION_NO_REPORT = '--noreport';

    /**
     * Will return executable php binary name
     * @Return string
     */
    protected function getExecutableBinaryName(): string
    {
        return self::EXECUTABLE_BINARY_NAME;
    }

    /**
     * Takes dir path and returns its tree structure in json format
     * Example:
     * ```
     * [
     * {"type":"directory","name":"/application/public/upload/files","size":4096,"contents":[
     *   {"type":"directory","name":"Documents","size":4096,"contents":[
     *     {"type":"file","name":"1123.pdf","size":184238},
     *     {"type":"directory","name":"Stuff","size":4096}
     *   ]},
     *   {"type":"directory","name":"Yyyyy","size":4096}
     * ]}
     * ]
     *```
     *
     * @param string $dirPath
     *
     * @return string
     *
     * @throws Exception
     */
    public function getDirJsonTree(string $dirPath, ): string
    {
        if (!file_exists($dirPath)) {
            throw new LogicException("{$dirPath} does not exist");
        }

        if (!is_dir($dirPath)) {
            throw new LogicException("{$dirPath} is not a folder");
        }

        if (!is_readable($dirPath)) {
            throw new LogicException("{$dirPath} is not readable");
        }

        $command = $this->buildCommand([
            $dirPath,
            self::ARG_JSON,
            self::ARG_GET_SIZE_BYTES,
            self::ARG_GET_ABSOLUTE_PATHS,
            self::ARG_GET_HIDDEN_FILES,
            self::OPTION_NO_REPORT,
        ]);

        $this->executeShellCommand($command);
        $process = $this->executeShellCommand($command);

        if (!$process->isSuccessful()) {
            throw new Exception("Command exited with FAILURE: {$command}");
        }

        return $process->getOutput();
    }

}