<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduleStart = Carbon::createFromTime(9, 0, 0);
        
        $clockIn = Carbon::instance($this->faker->dateTimeBetween('08:30:00', '10:00:00'));
        
        $lateMinutes = 0;
        $status = 'PRESENT';

        if ($clockIn->gt($scheduleStart)) {
            $lateMinutes = $clockIn->diffInMinutes($scheduleStart);
            $status = 'LATE';
        }

        $clockOut = Carbon::instance($this->faker->dateTimeBetween('17:00:00', '19:00:00'));
        return [
            'user_id' => User::factory(), // Default, will be overridden in seeder
            'date' => $this->faker->date(), // Will be overridden in seeder
            'clock_in_time' => $clockIn->format('H:i:s'),
            'clock_out_time' => $clockOut->format('H:i:s'),
            'late_minutes' => $lateMinutes,
            'status' => $status,
            'ip_address' => $this->faker->ipv4(),
            'notes' => $this->faker->optional(0.2)->sentence(), // 20% chance of notes
        ];
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ABSENT',
            'clock_in_time' => null,
            'clock_out_time' => null,
            'late_minutes' => 0,
            'ip_address' => null,
            'notes' => 'Unexplained Absence', // Optional
        ]);
    }
}
