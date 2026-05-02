<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MasterCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterCustomerFactory extends Factory
{
    protected $model = MasterCustomer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'normalized_name' => strtoupper(preg_replace('/[.,\s]/', '', $this->faker->name())),
            'is_company' => false,
            'customer_type' => 'individual',
            'telp_1' => $this->faker->phoneNumber(),
            'address_1' => $this->faker->streetAddress(),
            'full_address' => $this->faker->address(),
            'email' => $this->faker->unique()->safeEmail(),
            'source' => 'HRMSBY CV',
            'sources' => ['HRMSBY CV'],
            'data_quality_score' => $this->faker->numberBetween(20, 100),
            'sync_status' => 'pending',
        ];
    }
}
