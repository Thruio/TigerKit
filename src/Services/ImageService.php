<?php
namespace TigerKit\Services;

use TigerKit\Models;
use TigerKit\TigerException;

class ImageService extends BaseService
{
    /**
     * @return Models\Image[]
     */
    public function getAllImages()
    {
        return Models\Image::search()->where('deleted', "No")->exec();
    }

    /**
     * @param Models\User $user
     * @param $uploadFile
     * @return Models\Image
     */
    public function uploadImage(Models\User $user, $uploadFile)
    {
        $image = Models\Image::CreateFromUpload($uploadFile);
        $image->user_id = $user->user_id;
        $image->save();
        return $image;
    }

    /**
     * @param $tagName
     * @return Models\Image[]|false
     * @throws TigerException
     */
    public function getImagesByTag($tagName)
    {
        $tagService = new TagService();
        $tag = $tagService->getTagByName($tagName);
        if (!$tag) {
            throw new TigerException("No such tag '{$tagName}'.");
        }
        $imageTagLinks = Models\ImageTagLink::search()
        ->where('tag_id', $tag->tag_id)
        ->where('deleted', 'No')
        ->exec();
        $imageIds = [];
        foreach ($imageTagLinks as $imageTagLink) {
            /**
             * @var $imageTagLink Models\ImageTagLink
*/
            $imageIds[] = $imageTagLink->file_id;
        }
        return $this->getImagesByImageIds($imageIds);
    }

    /**
     * @param $imageIds
     * @return Models\Image[]|false
     */
    public function getImagesByImageIds($imageIds)
    {
        return Models\Image::search()
        ->where('file_id', $imageIds)
        ->where('deleted', 'No')
        ->exec();
    }

    public function addTag(Models\Image $image, Models\Tag $tag, Models\User $user = null)
    {
        $imageTagLink = new Models\ImageTagLink();
        $imageTagLink->file_id = $image->file_id;
        $imageTagLink->tag_id = $tag->tag_id;
        if ($user) {
            $imageTagLink->created_user = $user->user_id;
        }
        $imageTagLink->save();
        $tag->updatePopularityCount();
        return $imageTagLink;
    }

    public function addTags($images, $tags, Models\User $user = null)
    {
        if (!is_array($images)) {
            $images = [$images];
        }

        if (!is_array($tags)) {
            $tags = [$tags];
        }

        // Text to Objects..
        foreach ($tags as &$tag) {
            if (is_string($tag)) {
                $tag = TagService::CreateOrFind($tag);
            }
        }

        $imageTagLinks = [];
        foreach ($images as $image) {
            /**
             * @var $image Models\Image
*/
            foreach ($tags as $tag) {
                /**
                 * @var $tag Models\Tag
*/
                $imageTagLinks[] = $this->addTag($image, $tag, $user);
            }
        }
        return $imageTagLinks;
    }

    public function getRandomImage()
    {
        return Models\Image::search()
        ->where('deleted', 'No')
        ->order('rand()')
        ->execOne();
    }
}
