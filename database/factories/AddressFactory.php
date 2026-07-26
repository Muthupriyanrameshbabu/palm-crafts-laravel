<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->numerify('##########'),
            'line_1' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => 'Tamil Nadu',
            'postal_code' => $this->faker->numerify('######'),
            'country' => 'IN',
        ];
    }
}
