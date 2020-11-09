<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreControllerTest extends TestCase
{

    use DatabaseMigrations;
    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);

    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show',['genre'=> $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());

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


        $genre = factory(Genre::class)->create();
        $response =
            $this->json('PUT',route('genres.update',['genre'=>$genre->id]),[
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
        $response= $this->json('POST',route('genres.store'),[
            'name'=> 'testando'
        ]);

        $genre = Genre::find($response->json('id'));

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name'=> 'testando'
            ]);

    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'name'=>'teste',
            'is_active'=>false
        ]);

        $response= $this->json('PUT',route('genres.update',['genre'=>$genre->id]),[
            'name'=> 'test',
            'is_active'=>true

        ]);
        $genre = Genre::find($response->json('id'));
        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name'=>'test',
                'is_active'=>true
            ]);


    }

    public function testDestroy()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json('DELETE',route('genres.destroy',['genre'=>$genre->id]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($genre->id));
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
}
