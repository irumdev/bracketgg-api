<?php

declare(strict_types=1);

namespace Database\Factories\Team\Board;

use App\Models\Team\Board\ArticleImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Fake\Image as FakeImage;

class ArticleImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ArticleImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        if (config('app.test.useRealImage')) {
            return [
                'article_image' => FakeImage::create(storage_path('app/profileImages'), 640, 480, null, false)
            ];
        }
        return [
            'article_image' => FakeImage::url()
        ];
    }
}
