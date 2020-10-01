<?php


namespace Tests\Unit;


use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CategoryTest extends TestCase
{

    use DatabaseMigrations;

    public function  testDateAttribute(){

        $dates =['deleted_at','created_at','updated_at'];
        $category = new Category();
        foreach ($dates as $date) {
            $this->assertContains($date,$category->getDates());
        }

        $this->assertCount(count($dates),$category->getDates());
    }
    public function testFillableAttributes(){

        $fillable = ['name', 'description','is_active'];
        $category = new Category();
        $this->assertEquals(
            $fillable, $category->getFillable()
        );
    }

    public function testIfUseTraits(){
        Genre::create(['name'=>'test']);

        $traits = [
            SoftDeletes::class,Uuid::class
        ];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits,$categoryTraits);
    }

    public function  testCasts() {
        $cast =['id'=> 'string'];
        $category = new Category();
        $this->assertEquals($cast,$category->getCasts());
    }

    public function testIncrementing(){
        $category = new Category();
        $this->assertFalse($category->incrementing);
    }

}
