<?php

namespace App\Services;

use App\Controller\Utils\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class handles files tagging logic which is:
 * saving / removing/ adding/ updating tags
 * also it will handle the tag entity update upon moving the file to other directory via GUI
 * moving files outside of gui will not be supported here
 * # TODO: add custom command to run it in case when I move files outside of gui, it should:
 *      list the files that were moved with corresponding tags, and how tags will be reapplied after accepting suggested changes
 * Class FileTagger
 * @package App\Services
 */
class FileTagger {

    /**
     * get entity based on filename or fullpath
     * make prepare function where i set all vars as properties.
     * throw exception if no preparation was done
     * add isPrepared checker - if any var is not set - throw it
     * extraction of filename/extension etc. should be handled by FilesHandler.
     */

    const TAGGER_NOT_PREPARED_EXCEPTION_MESSAGE = "File tagger has not been prepared - did You call 'prepare()' method?";

    /**
     * @var string
     */
    private $module_name;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $directory_path;

    /**
     * @var string
     */
    private $full_file_path;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var Application  $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * Set the vars to handle tagging for current file
     * @param array $tags
     * @param string $full_file_path
     * @throws \Exception
     */
    public function prepare(array $tags, string $full_file_path) {

        if( empty($tags) ){
            throw new \Exception("Tags array is empty!");
        }

        $this->tags           = $tags;
        $this->filename       = FilesHandler::getFileNameFromFilePath($full_file_path);
        $this->module_name    = FilesHandler::getModuleNameForFilePath($full_file_path);
        $this->directory_path = FilesHandler::getDirectoryPathInModuleUploadDirForFilePath($full_file_path);
        $this->full_file_path = $full_file_path;
    }

    /**
     * @throws \Exception
     */
    private function getEntity(){
        $all_files_with_tags = $this->app->repositories->filesTagsRepository->findBy([
            'fullFilePath' => $this->full_file_path
        ]);

        $counted_files_with_tags = count($all_files_with_tags);

        if( $counted_files_with_tags > 1){
            throw new \Exception("More than one FileTags records were found for given path '{$this->full_file_path}'! ");
        }

        $file_with_tags = reset($all_files_with_tags);

        return $file_with_tags;
    }

    /**
     * @throws \Exception
     */
    private function updateTags(){

        if( !$this->isPrepared() ){
            throw new \Exception(static::TAGGER_NOT_PREPARED_EXCEPTION_MESSAGE);
        }

        $file_with_tags      = $this->getEntity();
        $current_tags_json   = $file_with_tags->getTags();
        $current_tags_array  = $this->jsonTagsToArray($current_tags_json);

        $new_tags = array_diff($this->tags, $current_tags_array);

        if( empty($new_tags) )
        {
            return new Response("There were no new tags to add");
        }

        try{

            $tags_array = array_merge($current_tags_array, $new_tags);
            $tags_json  = $this->arrayTagsToJson($tags_array);

            $file_with_tags->setTags($tags_json);

            $this->app->em->persist($file_with_tags);
            $this->app->em->flush();

        }catch(\Exception $e){
            return new Response("There was an error while updating the tags.");
        }

        return new Response("Tags have been updated successfully");
    }

    private function addTags(string $json, array $tags): string {
        if( !$this->isPrepared() ){
            throw new \Exception(static::TAGGER_NOT_PREPARED_EXCEPTION_MESSAGE);
        }

    }

    private function removeTags(array $tags): string {

        $array_of_tags = $this->jsonTagsToArray($json);
        $json          = '';

        return $json;
    }

    private function removeAllTags(string $tags){

    }

    private function jsonTagsToArray(string $json):array {

        $tags = [];
        return $tags;
    }

    private function arrayTagsToJson(array $tags): string{
        $json = '';
        return $json;
    }

    /**
     * Check if vars have been set
     */
    private function isPrepared(){

        if(
            !isset($this->module_name)
            ||  !isset($this->filename)
            ||  !isset($this->directory_path)
            ||  !isset($this->full_file_path)
            ||  !isset($this->tags)
        ){
            return false;
        }

        return true;
    }

}