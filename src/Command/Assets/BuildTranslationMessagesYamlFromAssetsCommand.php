<?php

namespace App\Command\Assets;


use App\Controller\Core\Application;
use App\Services\Files\Parser\YamlFileParserService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * Info: this could be cleaned up someday by replacing some logic with @see YamlFileParserService
 *
 * Strings present in this command shall not be moved to translation files ad this command DOES generates such
 * and thus it's required to see what's going on in case of crash
 * Class BuildTranslationMessagesYamlFromAssetsCommand
 * @package App\Command\Assets
 */
class BuildTranslationMessagesYamlFromAssetsCommand extends Command
{
    protected static $defaultName = 'assets:build-translation-messages-yaml';

    const TRANSLATION_ASSETS_FOLDER         = './src/assets/translations';
    const TRANSLATION_FOLDER_RELATIVE_PATH  = "./translations";
    const TRANSLATION_FILE_NAME             = "messages";
    const TRANSLATION_FILE_EXTENSION_YAML   = "yaml";

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var SymfonyStyle $io
     */
    private SymfonyStyle $io;

    /**
     * @var Parser $yamlParser
     */
    private Parser $yamlParser;

    public function __construct(Application $app, string $name = null) {
        parent::__construct($name);
        $this->app        = $app;
        $this->yamlParser = new YamlParser();
    }

    protected function configure()
    {
        $this
            ->setDescription("This command will get all the translations files from assets and wil build output bundle usable by symfony");
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->note("Starting building translation messages Yaml file");
        {
            $translationFilesData = $this->getTranslationFilesData();

            if( !empty($translationFilesData) ){
                $outputFilesPaths = $this->buildBundleTranslationFiles($translationFilesData);
                $this->validateOutputTranslationFiles($outputFilesPaths);
            }else{
                $message = "Translation data array is empty - does Your asset files even exist and are located in correct directory?";
                $io->warning($message);
                $this->app->logger->warning($message);
            }
        }
        $io->newLine();
        $io->success("Finished building translation messages Yaml file");

        return Command::SUCCESS;
    }

    private function getTranslationFilesData()
    {
        $this->app->logger->info("Started getting translation files data");

        $translationsAssetsDirectoryExist = file_exists(self::TRANSLATION_ASSETS_FOLDER);
        if( !$translationsAssetsDirectoryExist ){
            $this->app->logger->critical("Translations assets directory does not exist: ",[
                "directory" => self::TRANSLATION_ASSETS_FOLDER,
            ]);
            return false;
        }

        $finder = new Finder();
        $finder->in(self::TRANSLATION_ASSETS_FOLDER);

        $translationFilesData = [];

        /**
         * Iterate over all files for all languages
         * @var SplFileInfo $file
         */
        foreach( $finder->files() as $file ){
            $language        = $file->getRelativePath();
            $translationFile = $file->getRealPath();

            $this->app->logger->info("Found file ({$translationFile}) for language ({$language})");

            $outputBundleFileName = self::TRANSLATION_FILE_NAME . '.' . $language . '.' . self::TRANSLATION_FILE_EXTENSION_YAML;
            $outputBundleFilePath = self::TRANSLATION_FOLDER_RELATIVE_PATH . DIRECTORY_SEPARATOR . $outputBundleFileName;

            if( !key_exists($outputBundleFilePath, $translationFilesData) ){
                $translationFilesData[$outputBundleFilePath] = [];
            }

            $translationFileData = Yaml::parseFile($translationFile);

            // file might be empty, we must skip these or final parser will add invalid empty {}
            if( is_null($translationFileData) ){
                continue;
            }

            $translationFilesData[$outputBundleFilePath][] = $translationFileData;
        }

        return $translationFilesData;
    }

    /**
     * @param array $translationFilesData
     * @return array
     */
    private function buildBundleTranslationFiles(array $translationFilesData): array
    {
        $this->app->logger->info("Started building output messages file");

        $outputFilesPaths = [];

        // iterate over all language directories
        foreach($translationFilesData as $filePath => $assetsDataArrays ){

            // with this if there is duplicated key, parser will not cut it so we can validate duplicates
            $stringData = '';

            // iterate over array data created for each yaml file for given language
            foreach( $assetsDataArrays as $data_array ){
                $yamlDumpedData = Yaml::dump($data_array);
                $stringData    .= $yamlDumpedData;
            }

            file_put_contents($filePath, $stringData);
            $outputFilesPaths[] = $filePath;
        }
        $this->app->logger->info("Finished building output messages file");

        return $outputFilesPaths;
    }

    /**
     * @param array $outputFilesPaths
     */
    private function validateOutputTranslationFiles(array $outputFilesPaths): void
    {
        foreach($outputFilesPaths as $filePath){

            try{
                $this->yamlParser->parseFile($filePath, Yaml::PARSE_CONSTANT);
            }catch(Exception $e){
                $message = "File: {$filePath} is not valid - might contain duplicated keys, check it.";
                $this->io->error($message);
                $this->app->logger->critical($message);
            }

        }
    }
}