<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MasterVehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterVehicleFactory extends Factory
{
    protected $model = MasterVehicle::class;

    public function definition(): array
    {
        return [
            'registration_no' => strtoupper($this->faker->bothify('B #### ???')),
            'chassis_no' => $this->faker->bothify('MHL##############'),
            'engine_no' => $this->faker->bothify('4G##-######'),
            'model' => $this->faker->randomElement(['Xpander', 'Pajero Sport', 'L300']),
            'variant' => $this->faker->word(),
            'description' => $this->faker->sentence(3),
            'source' => 'HRMSBY CV',
            'sync_status' => 'pending',
        ];
    }
}
