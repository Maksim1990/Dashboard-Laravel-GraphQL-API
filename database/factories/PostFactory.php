<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    private const DEFAULT_SEPARATOR = '<br/><br/>';

    public function definition()
    {
        $userIds = User::all()->pluck('_id')->toArray();
        //$arrTypes = ['normal', 'tweet'];
        $arrTypes = ['normal']; //@TODO Currently set only normal posts
        return [
            'title' => $this->faker->realText(50),
            'description' => $this->getDescription(),
            'short_description' => $this->faker->realText(300, 3),
            'type' => $arrTypes[array_rand($arrTypes)],
            'user_id' => $userIds[array_rand($userIds)],
            'image_link' => sprintf('https://source.unsplash.com/random/500x200?sig=%s', rand(1, 100)),
            'created_at' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s')
        ];
    }

    private function getDescription(): string
    {
        $description = null;
        for ($i = 0; $i <= rand(4, 7); $i++) {
            $description .= sprintf(
                '%s%s%s%s',
                $this->faker->paragraph(rand(30, 70), true),
                self::DEFAULT_SEPARATOR,
                $this->getFakeImage($i),
                self::DEFAULT_SEPARATOR,
            );
        }

        return $description;
    }

    private function getFakeImage(int $randomImage): string
    {
        return sprintf(
            '<img src="https://source.unsplash.com/random/500x200?sig=%s" alt="Image" height="500" class="rounded-0 post-image card-img">',
            $randomImage
        );
    }
}
