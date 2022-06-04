<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition()
    {
        $userIds = User::all()->pluck('_id')->toArray();
        return [
            'title' => Str::random(10),
            'description' => Str::random(10) . ' descr',
            'created_by' => $userIds[array_rand($userIds)],
            'created_at' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s')
        ];
    }
}
