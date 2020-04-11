<?php

namespace App\Services\Files;

use App\Controller\Core\Application;
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

    const KEY_TAGS = 'tags';

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
            $message = $this->app->translator->translate('exceptions.tagger.moreThanOneFileTagsRecordsFoundForPath') . $file_full_path;
            throw new \Exception($message);
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
            $message = $this->app->translator->translate('exceptions.tagger.allTagsHaveBeenRemoved');
            throw new \Exception($message);
        }

        try {

            $file_with_tags = $this->getEntity();

            # no tags exist for that file, add them, or do nothing
            if( empty($file_with_tags) && !empty($this->tags) ){
                $tags_json = $this->arrayTagsToJson($this->tags);

                $file_tags = new FilesTags();
                $file_tags->setFullFilePath($this->full_file_path);
                $file_tags->setTags($tags_json);

                $this->app->em->persist($file_tags);
                $this->app->em->flush();

                $message = $this->app->translator->translate('responses.tagger.tagsHaveBeenCreated');
                return new Response($message);
            }

            # no tags exist and not adding any
            if ( empty($file_with_tags) && empty($this->tags) ){
                $message = $this->app->translator->translate('responses.tagger.noTagsToAdd');
                return new Response($message);
            }

            # tags exist but we just removed them all
            if(
                    // either there are not tags at all
                (
                        !empty($file_with_tags)
                    &&   empty($this->tags)
                )
                ||  // or there is just one tag but it's empty
                (
                        count($this->tags) === 1
                    &&  array_key_exists(0, $this->tags)
                    &&  empty( reset($this->tags) )
                )
                ){
                $this->app->em->remove($file_with_tags);
                $this->app->em->flush();

                $message = $this->app->translator->translate('responses.tagger.allTagsHaveBeenRemoved');
                return new Response($message);
            }

            $current_tags_json  = $file_with_tags->getTags();
            $current_tags_array = $this->jsonTagsToArray($current_tags_json);

            $new_tags           = array_diff($this->tags, $current_tags_array);
            $common_tags        = array_intersect($this->tags, $current_tags_array);

            $are_tags_removed   = ( count($current_tags_array) !== count($common_tags) );

            if ( empty($new_tags) && !$are_tags_removed ) {
                $message = $this->app->translator->translate('responses.tagger.noTagsToAdd');
                return new Response($message);
            }

            $tags_array = array_merge($new_tags, $common_tags);
            $tags_json  = $this->arrayTagsToJson($tags_array);

            $file_with_tags->setTags($tags_json);

            $this->app->em->persist($file_with_tags);
            $this->app->em->flush();

            $message = $this->app->translator->translate('responses.tagger.tagsUpdated');
            return new Response($message);

        } catch (\Exception $e) {
            $message = $this->app->translator->translate('exceptions.tagger.thereWasAnError');
            return new Response($message);
        }

    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function removeTags(){

        $file_with_tags = $this->getEntity();

        if( empty($file_with_tags) ){
            $message = $this->app->translator->translate('responses.tagger.noTagsToRemove');
            return new Response($message);
        }else{
            $this->app->em->remove($file_with_tags);
            $this->app->em->flush();

            $message = $this->app->translator->translate('responses.tagger.allTagsHaveBeenRemoved');
            return new Response($message);
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
                !isset($this->full_file_path)
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