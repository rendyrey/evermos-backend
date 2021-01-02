<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_type_id' => $this->faker->numberBetween(1, 100),
            'name' => $this->faker->firstName,
            'quantity' => $this->faker->numberBetween(1, 1000),
            'price' => $this->faker->randomFloat(2, 1, 500),
        ];
    }
}
