<?php

namespace TigerKit\Test\Services;

use TigerKit\Services\ImageService;
use TigerKit\Services\TagService;
use TigerKit\Models\Image;
use TigerKit\Test\TigerBaseTest;

class ImageServiceTest extends TigerBaseTest
{

  /** @var ImageService */
  private $imageService;

  public function setUp()
  {
    parent::setUp();
    $this->imageService = new ImageService();
  }

  private function makeMockUpload($mockAssetPath){
    $mockAssetTmpLocation = tempnam(sys_get_temp_dir(), "test-");
    copy($mockAssetPath, $mockAssetTmpLocation);
    $mockUpload = [
      'name' => basename($mockAssetPath),
      'type' => "image/jpeg",
      'tmp_name' => $mockAssetTmpLocation,
      'error' => 0,
      'size' => filesize($mockAssetPath),
    ];
    return $mockUpload;
  }

  public function testUploadImage()
  {
    $mockAsset = __DIR__ . "/../Assets/sample-1.jpg";
    $mockUpload = $this->makeMockUpload($mockAsset);

    $image = $this->imageService->uploadImage($this->testUser, $mockUpload);
    $this->assertTrue($image instanceof Image);
    $this->assertEquals(2448, $image->width);
    $this->assertEquals(3264, $image->height);
    $this->assertGreaterThan(0, $image->file_id);
    $this->assertEquals($this->testUser->user_id, $image->user_id);
    $this->assertEquals("image/jpeg", $image->filetype);
    $this->assertEquals(filesize($mockAsset), $image->filesize);
    $this->assertGreaterThan(time() - 3, strtotime($image->created));
    $this->assertGreaterThan(time() - 3, strtotime($image->updated));
    return $image;
  }

  public function testGetAllImages()
  {
    $images = $this->imageService->getAllImages();
    $this->assertTrue(is_array($images));
    $this->assertGreaterThanOrEqual(1, count($images));
    $this->assertTrue(end($images) instanceof Image);

  }

  /**
   * @depends testUploadImage
   */
  public function testRetrieveImageData(Image $image)
  {
    $data = $image->getData();
    $this->assertEquals($image->filesize, strlen($data));
  }

  /**
   * @depends testUploadImage
   */
  public function testRetrieveImageDataStream(Image $image)
  {
    $stream = $image->getDataStream();
    $data = stream_get_contents($stream);
    $this->assertEquals($image->filesize, strlen($data));
  }

  /**
   * @depends testUploadImage
   */
  public function testReplaceImageData(Image $image){
    $mockAsset = __DIR__ . "/../Assets/sample-2.jpg";
    $image->putData(file_get_contents($mockAsset));
    $this->assertEquals(filesize($mockAsset), $image->filesize);
    return $image;
  }

  /**
   * @depends testReplaceImageData
   */
  public function testReplaceImageDataStream(Image $image){
    $mockAsset = __DIR__ . "/../Assets/sample-3.jpg";
    $image->putDataStream(fopen($mockAsset, 'r'));
    $this->assertEquals(filesize($mockAsset), $image->filesize);
  }

  public function testTagImages(){
    $attackRabbitAsset = __DIR__ . "/../Assets/sample-1.jpg";
    $attackRabbitMockUpload = $this->makeMockUpload($attackRabbitAsset);
    $attackRabbitImage = $this->imageService->uploadImage($this->testUser, $attackRabbitMockUpload);

    $carsAsset = __DIR__ . "/../Assets/sample-1.jpg";
    $carsMockUpload = $this->makeMockUpload($carsAsset);
    $carsImage = $this->imageService->uploadImage($this->testUser, $carsMockUpload);

    $hompfCatAsset = __DIR__ . "/../Assets/sample-1.jpg";
    $hompfCatMockUpload = $this->makeMockUpload($hompfCatAsset);
    $hompfCatImage = $this->imageService->uploadImage($this->testUser, $hompfCatMockUpload);

    $this->imageService->addTag($attackRabbitImage, TagService::CreateOrFind("rabbit"));
    $this->imageService->addTag($carsImage, TagService::CreateOrFind("cars"));
    $this->imageService->addTag($hompfCatImage, TagService::CreateOrFind("cat"));

    $this->imageService->addTags([$attackRabbitImage, $carsImage, $hompfCatImage], "test");
    $this->imageService->addTags([$attackRabbitImage, $carsImage, $hompfCatImage], ["test2"]);

  }

}
