<?php

use Illuminate\Database\Seeder;

class VideoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private $allGenres;
    private $relations = [
        'genres_id' => [],
        'categories_id' => [],
    ];

    public function run()
    {
        //factory(\App\Models\Video::class,100)->create();

        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);

        $self = $this;
        $this->allGenres = \App\Models\Genre::all();
        \Illuminate\Database\Eloquent\Model::reguard(); //Active mass assignment in the model
        factory(\App\Models\Video::class, 100)
            ->make()
            ->each(function (\App\Models\Video $video) use ($self) {
                $self->fetchRelations();
                \App\Models\Video::create(
                    array_merge(
                        $video->toArray(),
                        [
                        ],
                        $this->relations
                    )
                );
            });
        \Illuminate\Database\Eloquent\Model::unguard(); //
    }
    public function fetchRelations()
    {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesId = [];
        //Using operator spread PHP ... for get [1,2,3,4,5]
        foreach ($subGenres as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $subGenresId = $subGenres->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genres_id'] = $subGenresId;
    }

}
