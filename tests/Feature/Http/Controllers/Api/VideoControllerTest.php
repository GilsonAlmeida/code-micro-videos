<?php

namespace Tests\Feature\Http\Controllers\Api;


use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;


class VideoControllerTest extends TestCase
{

    use DatabaseMigrations,TestValidations,TestSaves;

    private $video;
    private $sendData;

    protected function setUp():void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
        $this->sendData = [
            'title'=>'title',
            'description'=>'description',
            'year_launched'=>2010,
            'rating'=>Video::RATING_LIST[0],
            'duration'=>90,

        ];
    }


    public function testShow()
    {
        $response = $this->get(route('videos.show',['video'=> $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());

    }
    public function testIndex()
    {

        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);

    }
//
    public function testInvalidationRequired()
    {
        $data=[
            'title'=>'',
            'description'=>'',
            'year_launched'=>'',
            'rating'=>'',
            'duration'=>''
        ];
        $this->assertInvalidationStoreAction($data,'required');
        $this->assertInvalidationUpdateAction($data,'required');
    }


    public function  testInvalidationMax()
    {
        $data = [
            'title'=>str_repeat('a',256)
        ];
        $this->assertInvalidationStoreAction($data,'max.string',['max'=>255]);
        $this->assertInvalidationUpdateAction($data,'max.string',['max'=>255]);
    }

    public function testInvalidationInteger(){
        $data = [
            'duration'=>'s'
        ];
        $this->assertInvalidationStoreAction($data,'integer');
        $this->assertInvalidationUpdateAction($data,'integer');
    }

    public function testInvalidationYearLaunchedField(){
        $data = [
            'year_launched'=>'a'
        ];
        $this->assertInvalidationStoreAction($data,'date_format',['format'=>'Y']);
        $this->assertInvalidationUpdateAction($data,'date_format',['format'=>'Y']);
    }

    public function testInvalidationOpenedField(){
        $data = [
            'opened'=>'s'
        ];
        $this->assertInvalidationStoreAction($data,'boolean');
        $this->assertInvalidationUpdateAction($data,'boolean');
    }

    public function testInvalidationRatingField(){
        $data = [
            'rating'=>0
        ];
        $this->assertInvalidationStoreAction($data,'in');
        $this->assertInvalidationUpdateAction($data,'in');
    }

    public function testStore()
    {
        $this->assertStore($this->sendData,$this->sendData+['opened'=>0]);

    }
//
    public function testUpdate()
    {
        $this->assertUpdate($this->sendData,$this->sendData+['opened'=>0]);
    }
//
    public function testDestroy()
    {
        $response = $this->json('DELETE',route('videos.destroy',['video'=>$this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }


    protected function model()
    {
        return Video::class;
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update',['video'=>$this->video->id]);
    }
}
