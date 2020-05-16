<?php

namespace App\Command\Assets;


use App\Controller\Core\Application;
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
    private $app;

    /**
     * @var SymfonyStyle $io
     */
    private $io = null;

    /**
     * @var Parser $yamlParser
     */
    private $yamlParser;

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
            $translation_files_data = $this->getTranslationFilesData();

            if( !empty($translation_files_data) ){
                $output_files_paths = $this->buildBundleTranslationFiles($translation_files_data);
                $this->validateOutputTranslationFiles($output_files_paths);
            }else{
                $message = "Translation data array is empty - does Your asset files even exist and are located in correct directory?";
                $io->warning($message);
                $this->app->logger->warning($message);
            }
        }
        $io->newLine();
        $io->success("Finished building translation messages Yaml file");
    }

    private function getTranslationFilesData()
    {
        $this->app->logger->info("Started getting translation files data");

        $translations_assets_directory_exist = file_exists(self::TRANSLATION_ASSETS_FOLDER);
        if( !$translations_assets_directory_exist ){
            $this->app->logger->critical("Translations assets directory does not exist: ",[
                "directory" => self::TRANSLATION_ASSETS_FOLDER,
            ]);
            return false;
        }

        $finder = new Finder();
        $finder->in(self::TRANSLATION_ASSETS_FOLDER);

        $translation_files_data = [];

        /**
         * Iterate over all files for all languages
         * @var SplFileInfo $file
         */
        foreach( $finder->files() as $file ){
            $language         = $file->getRelativePath();
            $translation_file = $file->getRealPath();

            $this->app->logger->info("Found file ({$translation_file}) for language ({$language})");

            $output_bundle_file_name = self::TRANSLATION_FILE_NAME . '.' . $language . '.' . self::TRANSLATION_FILE_EXTENSION_YAML;
            $output_bundle_file_path = self::TRANSLATION_FOLDER_RELATIVE_PATH . DIRECTORY_SEPARATOR . $output_bundle_file_name;

            if( !key_exists($output_bundle_file_path, $translation_files_data) ){
                $translation_files_data[$output_bundle_file_path] = [];
            }

            $translation_file_data = Yaml::parseFile($translation_file);

            // file might be empty, we must skip these or final parser will add invalid empty {}
            if( is_null($translation_file_data) ){
                continue;
            }

            $translation_files_data[$output_bundle_file_path][] = $translation_file_data;
        }

        return $translation_files_data;
    }

    /**
     * @param array $translation_files_data
     * @return array
     */
    private function buildBundleTranslationFiles(array $translation_files_data): array
    {
        $this->app->logger->info("Started building output messages file");

        $output_files_paths = [];

        // iterate over all language directories
        foreach( $translation_files_data as $file_path => $assets_data_arrays ){

            // with this if there is duplicated key, parser will not cut it so we can validate duplicates
            $string_data = '';

            // iterate over array data created for each yaml file for given language
            foreach( $assets_data_arrays as $data_array ){
                $yaml_dumped_data = Yaml::dump($data_array);
                $string_data     .= $yaml_dumped_data;
            }

            file_put_contents($file_path, $string_data);
            $output_files_paths[] = $file_path;
        }
        $this->app->logger->info("Finished building output messages file");

        return $output_files_paths;
    }

    /**
     * @param array $output_files_paths
     */
    private function validateOutputTranslationFiles(array $output_files_paths): void
    {
        foreach( $output_files_paths as $file_path){

            try{
                $this->yamlParser->parseFile($file_path, Yaml::PARSE_CONSTANT);
            }catch(Exception $e){
                $message = "File: {$file_path} is not valid - might contain duplicated keys, check it.";
                $this->io->error($message);
                $this->app->logger->critical($message);
            }

        }
    }
}