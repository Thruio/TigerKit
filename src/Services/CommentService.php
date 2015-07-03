<?php
namespace TigerKit\Services;

use TigerKit\Models;

class CommentService extends BaseService {

  /**
   * @param Models\Comment $comment
   * @param Models\File $file
   * @return Models\ImageCommentLink
   */
  public function addCommentToFile(Models\Comment $comment, Models\File $file){
    $link = new Models\ImageCommentLink();
    $link->comment_id = $comment->comment_id;
    $link->file_id = $file->file_id;
    $link->save();
    return $link;
  }

  /**
   * @param Models\Comment $comment
   * @param Models\Image $image
   * @return Models\ImageCommentLink
   */
  public function addCommentToImage(Models\Comment $comment, Models\Image $image){
    return $this->addCommentToFile($comment, $image);
  }

  /**
   * @param Models\File $file
   * @return Models\Comment[]|false
   */
  public function getComments(Models\File $file){
    $links = Models\ImageCommentLink::search()
      ->where('file_id', $file->file_id)
      ->where('deleted', 'No')
      ->exec();
    $comment_ids = [];
    foreach($links as $link){
      /** @var $link Models\ImageCommentLink */
      $comment_ids[] = $link->comment_id;
    }
    return Models\Comment::search()->where('comment_id', $comment_ids, 'IN')->exec();
  }
}
