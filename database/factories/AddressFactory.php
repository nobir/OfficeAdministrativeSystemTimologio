<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'local_address' => $this->faker->address,
            'police_station' => $this->faker->name,
            'city' => $this->faker->city,
            'country' => $this->faker->country,
            'zip_code' => $this->faker->postcode,
        ];
    }
}
