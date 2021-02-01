<?php

namespace Database\Factories\Team\Board;

use App\Models\Team\Board\ArticleImageBuffer;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\Fake\Image as FakeImage;

class ArticleImageBufferFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ArticleImageBuffer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        if (config('app.test.useRealImage')) {
            return [
                'buffer_image_path' => FakeImage::retryCreate(storage_path('app/profileImages'), 640, 480, null, false)
            ];
        }
        return [
            'buffer_image_path' => FakeImage::url()
        ];
    }
}
