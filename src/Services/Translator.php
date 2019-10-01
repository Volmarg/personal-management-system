<?php

namespace App\Services;

use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

/**
 * Instead of using built in trans() i use this to provide my own rules/logic for handling translations
 * Class Translations
 * @package App\Controller\Utils
 */
class Translator extends AbstractController {

    const TRANSLATIONS_EXTENSION = 'yaml';

    const TRANSLATIONS_DIR      = '../translations'; // relative to current dir

    const DEFAULT_LOCALE        = 'en';

    /**
     * @var string[] $translations_files_paths
     */
    private $translations_files_paths = [];

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * @var bool $tr
     */
    private $translation_resources_added = false;

    /**
     * @var string $user_defined_locale
     */
    private $user_defined_locale = '';

    public function __construct($locale = self::DEFAULT_LOCALE){

        $this->user_defined_locale = $locale;

        $this->finder     = new Finder();
        $this->translator = $this->getTranslator($locale);

    }

    /**
     * Translates a string
     * @param string $searched_key
     * @return string
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function translate(string $searched_key): string {

        $this->init();
        $this->checkDuplicatedKeys($searched_key);

        $translation = $this->translator->trans($searched_key);

        return $translation;
    }

    /**
     * This function will find all translation files and add the once to translator on creating self instance
     */
    private function init() {
        if ( !$this->translation_resources_added ) {
            $this->findAllTranslationFiles();
            $this->addTranslatorResources();
            $this->translation_resources_added = true;
        }
    }

    /**
     * Adds all translations files to translator so translator will search for key in set built from this files data
     */
    private function addTranslatorResources(): void {
        foreach( $this->translations_files_paths as $file_path) {
            $this->translator->addResource(static::TRANSLATIONS_EXTENSION, $file_path, $this->user_defined_locale);
        }
    }

    /**
     * This method will search for translation in all provided files,
     * This solution is most optimal as array_intersect cannot handle subarrays
     * Also another thing is that I want to check if there is a translation for full key "XX.ZZ.YY etc"
     * @param string $searched_key
     * @throws ExceptionDuplicatedTranslationKey
     */
    private function checkDuplicatedKeys(string $searched_key): void {

        $is_translation_found = false;
        $found_in_file        = '';
        $found_key            = '';

        /**
         * Now foreach call we build new Translator because we want to operate only on one resource at time
         */
        foreach($this->translations_files_paths as $file_path ){

            $translator = $this->getTranslator($this->user_defined_locale);
            $translator->addResource(static::TRANSLATIONS_EXTENSION, $file_path, $this->user_defined_locale);

            $translationOutput = $translator->trans($searched_key);

            # In normal case if key was not found then searched key is returned
            if( $translationOutput !== $searched_key ){

                if( $is_translation_found && $found_key === $searched_key ){
                    $duplicate_found_in_file = $file_path;
                    throw new ExceptionDuplicatedTranslationKey($searched_key, $found_in_file, $duplicate_found_in_file);
                }

                $is_translation_found = true;
                $found_key            = $searched_key;
                $found_in_file        = $file_path;
            }

            unset($translator);

        }

    }

    /**
     * Searches for translation files within <ProjectRootDir>/translations
     */
    private function findAllTranslationFiles(): void {

        $this->finder->name('*.' . static::TRANSLATIONS_EXTENSION)->in(static::TRANSLATIONS_DIR);

        foreach( $this->finder as $index => $file ){
            $this->translations_files_paths[] = $file->getPathname();
        }

    }

    /**
     * This function is used to get fresh translator
     * @param string $locale
     * @return SymfonyTranslator
     */
    private function getTranslator($locale = self::DEFAULT_LOCALE): SymfonyTranslator {
        $translator = new SymfonyTranslator($locale);
        $translator->addLoader(static::TRANSLATIONS_EXTENSION, new YamlFileLoader());

        return $translator;
    }

}