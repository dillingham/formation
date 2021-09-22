<?php

namespace Dillingham\Formation\Tests\Fixtures\Database\Factories;

use Dillingham\Formation\Tests\Fixtures\Post;
use Dillingham\Formation\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'body' => $this->faker->sentence,
            'author_id' => User::factory(),
        ];
    }
}
