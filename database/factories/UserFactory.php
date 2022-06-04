<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        $enabled = rand(0, 1) == 1;
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => Hash::make('Password!123'),
            'remember_token' => Str::random(10),
            'confirmation_token' => !$enabled ? bin2hex(random_bytes(32)) : '',
            'enabled' => $enabled,
            'role' => 'user',
            'created_at' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
            'country' => $this->faker->country(),
            'country_code' => $this->faker->countryCode(),
            'city' => $this->faker->city(),
            'zip' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'avatar' => $this->getFakeAvatarImage(),
            'birthdate' => $this->faker->dateTimeBetween('-50 years')->format('Y-m-d'),
            'bio' => $this->faker->realText(),
        ];
    }

    private function getFakeAvatarImage(): string
    {
        return sprintf('https://i.pravatar.cc/300?u=%s', $this->faker->uuid());
    }
}
