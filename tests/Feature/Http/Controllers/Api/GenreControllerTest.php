<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestSaves;



class GenreControllerTest extends TestCase
{

    use DatabaseMigrations,TestSaves;

    private $genre;

    public function testIndex()
    {
        $this->genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);

    }

    public function testShow()
    {
        $this->genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show',['genre'=> $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());

    }

    public  function  testInvalidationData()
    {

        $response = $this->json('POST',route('genres.store'),[]);
        $this->assertInvalidarionRequired($response);

        $response = $this->json('POST',route('genres.store'),[
            'name'=>str_repeat('a',256),
            'is_active'=>'a'
        ]);
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name','is_active'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.string',['attribute'=>'name','max'=>255])]
            )
            ->assertJsonFragment([
                \Lang::get('validation.boolean',['attribute'=> 'is active']),
            ]);


        $this->genre = factory(Genre::class)->create();
        $response =
            $this->json('PUT',route('genres.update',['genre'=>$this->genre->id]),[
                'name'=>str_repeat('a',256),
                'is_active'=> 'a'
            ]);
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name','is_active'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.string',['attribute'=>'name','max'=>255])]
            )
            ->assertJsonFragment([
                \Lang::get('validation.boolean',['attribute'=> 'is active']),
            ]);
    }

    public function testStore()
    {
        $data =[
            'name' =>'test'
        ];
        $this->assertStore($data,$data+['is_active'=>1, 'deleted_at'=>null]);

        $data =[
            'name'=> 'test',
            'is_active'=>0
        ];
        $this->assertStore($data,$data+['is_active'=>0]);

    }

    public function testUpdate()
    {
       $this->genre = factory(Genre::class)->create([
            'name'=>'teste',
            'is_active'=>false
        ]);

        $data=[
            'name'=> 'test',
            'is_active'=>true
        ];
        $this->assertUpdate($data,$data+['deleted_at'=>null]);


    }

    public function testDestroy()
    {
        $this->genre = factory(Genre::class)->create();
        $response = $this->json('DELETE',route('genres.destroy',['genre'=>$this->genre->id]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
    }

    private function assertInvalidarionRequired(TestResponse  $response){

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment(
                [\Lang::get('validation.required',['attribute'=>'name'])]
            );

    }

    protected function model()
    {
        return Genre::class;
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update',['genre'=>$this->genre->id]);
    }
}
