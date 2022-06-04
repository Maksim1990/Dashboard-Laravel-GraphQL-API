<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    public function definition()
    {
        $userIds = User::all()->pluck('_id')->toArray();

        $from_user_id = $userIds[array_rand($userIds)];
        $user_id = $userIds[array_rand($userIds)];
        while ($from_user_id === $user_id) {
            $from_user_id = $userIds[array_rand($userIds)];
            $user_id = $userIds[array_rand($userIds)];
        }
        $arrTypes = ['text', 'image'];
        return [
            'text' => $this->faker->text,
            'from_user_id' => $from_user_id,
            'user_id' => $user_id,
            'file_id' => 0,
            'is_read' => rand(0, 1) == 1,
            // 'type' => $arrTypes[array_rand($arrTypes)],
            'type' => "text",
            'created_at' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s')
        ];
    }
}
