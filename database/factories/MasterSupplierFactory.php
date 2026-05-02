<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MasterSupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterSupplierFactory extends Factory
{
    protected $model = MasterSupplier::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('SUP-####'),
            'name' => $this->faker->company(),
            'address_1' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'sync_status' => 'pending',
        ];
    }
}
