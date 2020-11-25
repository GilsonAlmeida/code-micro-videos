<?php

namespace Tests\Feature\Http\Controllers\Api;




use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;


class CategoryControllerTest extends TestCase
{

  use DatabaseMigrations;
    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);

    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show',['category'=> $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());

    }

    public  function  testInvalidationData()
    {
        $response = $this->json('POST',route('categories.store'),[]);
        $this->assertInvalidarionRequired($response);

        $response = $this->json('POST',route('categories.store'),[
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


        $category = factory(Category::class)->create();
        $response =
            $this->json('PUT',route('categories.update',['category'=>$category->id]),[
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
        $response= $this->json('POST',route('categories.store'),[
            'name'=> 'test'
        ]);
        $category = Category::find($response->json('id'));
        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertEquals(1,$response->json('is_active'));
        $this->assertNull($response->json('description'));

        //

        $response= $this->json('POST',route('categories.store'),[
            'name'=> 'test',
            'description'=>'teste',
            'is_active'=>false

        ]);

        $response
            ->assertJsonFragment([
                'is_active'=> 0,
                'description'=> 'teste'
            ]);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description'=>'teste',
            'is_active'=>false
        ]);

        $response= $this->json('PUT',route('categories.update',['category'=>$category->id]),[
            'name'=> 'test',
            'description'=>'terror',
            'is_active'=>true

        ]);
        $category = Category::find($response->json('id'));
        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'description'=>'terror',
                'is_active'=>true
            ]);
    }

    public function testDestroy()
    {
        $category = factory(Category::class)->create();
        $response = $this->json('DELETE',route('categories.destroy',['category'=>$category->id]));
        $response->assertStatus(204);
        $this->assertNull(Category::find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));
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
