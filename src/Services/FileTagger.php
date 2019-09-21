<?php

namespace App\Services;

use App\Controller\Utils\Application;
use App\Entity\FilesTags;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class handles files tagging logic which is:
 * saving / removing/ adding/ updating tags
 * also it will handle the tag entity update upon moving the file to other directory via GUI
 * moving files outside of gui will not be supported here
 * Class FileTagger
 * @package App\Services
 */
class FileTagger {

    const TAGGER_NOT_PREPARED_EXCEPTION_MESSAGE = "File tagger has not been prepared - did You call 'prepare()' method?";
    const NO_TAGS_TO_ADD_RESPONSE               = "There were no new tags to add";
    const ALL_TAGS_HAVE_BEEN_REMOVED_RESPONSE   = "All tags have been removed.";
    const KEY_TAGS                              = 'tags';
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
     * All the tags from input must be passed in as the difference between what's in DB will handle the corresponding action
     * @param array $tags - empty is ok, this means we remove all tags
     * @param string $full_file_path
     * @throws \Exception
     */
    public function prepare(array $tags, string $full_file_path) {
        $this->tags           = $tags;
        $this->filename       = FilesHandler::getFileNameFromFilePath($full_file_path);
        $this->module_name    = FilesHandler::getModuleNameForFilePath($full_file_path);
        $this->directory_path = FilesHandler::getDirectoryPathInModuleUploadDirForFilePath($full_file_path);
        $this->full_file_path = $full_file_path;
    }

    /**
     * This method will get the fileTags entity for full file path,
     * By default the full file path passed as param will be used but if param is passed then it will be used in search
     * @param string|null $file_full_path
     * @return FilesTags
     * @throws \Exception
     */
    private function getEntity(? string $file_full_path = null): ?FilesTags {

        $file_full_path = ( is_null($file_full_path) ? $this->full_file_path : $file_full_path );


        $all_files_with_tags = $this->app->repositories->filesTagsRepository->findBy([
            'fullFilePath' => $file_full_path
        ]);

        $counted_files_with_tags = count($all_files_with_tags);

        if( $counted_files_with_tags > 1 ){
            throw new \Exception("More than one FileTags records were found for given path '{$file_full_path}'! ");
        }

        if( empty($all_files_with_tags) ){
            return null;
        } else {
            $file_with_tags = reset($all_files_with_tags);

        }

        return $file_with_tags;
    }

    /**
     * This function handles adding/removing tags
     * @throws \Exception
     */
    public function updateTags(){

        if( !$this->isPrepared() ){
            throw new \Exception(static::TAGGER_NOT_PREPARED_EXCEPTION_MESSAGE);
        }

        try {

            $file_with_tags = $this->getEntity();

            # no tags exist for that file, add them, or do nothing
            if( empty($file_with_tags) && !empty($this->tags) ){
                $tags_json = $this->arrayTagsToJson($this->tags);

                $file_tags = new FilesTags();
                $file_tags->setFullFilePath($this->full_file_path);
                $file_tags->setModuleName($this->module_name);
                $file_tags->setFilename('test');
                $file_tags->setDirectoryPath($this->directory_path);
                $file_tags->setTags($tags_json);

                $this->app->em->persist($file_tags);
                $this->app->em->flush();

                return new Response("Tags have been created successfully.");
            }

            # no tags exist and not adding any
            if ( empty($file_with_tags) && empty($this->tags) ){
                return new Response(static::NO_TAGS_TO_ADD_RESPONSE);
            }

            # tags exist but we just removed them all
            if( !empty($file_with_tags) && empty($this->tags) ){
                $this->app->em->remove($file_with_tags);
                $this->app->em->flush();

                return new Response(static::ALL_TAGS_HAVE_BEEN_REMOVED_RESPONSE);
            }

            $current_tags_json  = $file_with_tags->getTags();
            $current_tags_array = $this->jsonTagsToArray($current_tags_json);

            $new_tags           = array_diff($this->tags, $current_tags_array);
            $common_tags        = array_intersect($this->tags, $current_tags_array);

            $are_tags_removed   = ( count($current_tags_array) !== count($common_tags) );

            if ( empty($new_tags) && !$are_tags_removed ) {
                return new Response(static::NO_TAGS_TO_ADD_RESPONSE);
            }

            $tags_array = array_merge($new_tags, $common_tags);
            $tags_json  = $this->arrayTagsToJson($tags_array);

            $file_with_tags->setTags($tags_json);

            $this->app->em->persist($file_with_tags);
            $this->app->em->flush();

            return new Response("Tags have been updated successfully");

        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return new Response("There was an error while updating the tags.");
        }

    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function removeTags(){

        $file_with_tags = $this->getEntity();

        # no tags exist for that file, add them, or do nothing
        if( empty($file_with_tags) ){
            return new Response("There were no tags to remove");
        }else{
            $this->app->em->remove($file_with_tags);
            $this->app->em->flush();
            return new Response(static::ALL_TAGS_HAVE_BEEN_REMOVED_RESPONSE);
        }

    }

    private function jsonTagsToArray(string $json): array {
        $tags = \GuzzleHttp\json_decode($json, true);
        return $tags;
    }

    private function arrayTagsToJson(array $tags): string{
        $json = \GuzzleHttp\json_encode($tags);
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

    /**
     * @param string $old_file_path
     * @param string $new_file_path
     * @throws \Exception
     */
    public function updateFilePath(string $old_file_path, string $new_file_path) {

        $file_tags = $this->getEntity($old_file_path);

        if( !$file_tags ){
            return;
        }

        $file_tags->setFullFilePath($new_file_path);

        $this->app->em->persist($file_tags);
        $this->app->em->flush();
    }

    /**
     * @param string $old_folder_path
     * @param string $new_folder_path
     * @throws \Exception
     */
    public function updateFilePathByFolderPathChange(string $old_folder_path, string $new_folder_path) {
        $this->app->repositories->filesTagsRepository->updateFilePathByFolderPathChange($old_folder_path, $new_folder_path);
    }

    /**
     * This will copy the current set of tag and create new set with new path
     * @param string $current_file_path
     * @param string $copy_file_path
     * @throws \Exception
     */
    public function copyTagsFromPathToNewPath(string $current_file_path, string $copy_file_path): void {

        $tags_arr  = [];
        $file_tags = $this->getEntity($current_file_path);

        if( !empty($file_tags) ){
            $tags_json = $file_tags->getTags();
            $tags_arr  = \GuzzleHttp\json_decode($tags_json);
        }

        $this->prepare($tags_arr, $copy_file_path);
        $this->updateTags();

    }

}