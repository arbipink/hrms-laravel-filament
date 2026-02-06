<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Fine;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    protected const LATE_THRESHOLD_MINUTES = 15;
    protected const FINE_THRESHOLD_MINUTES = 60;

    protected const SHIFTS = [
        ['start' => '08:00:00', 'end' => '17:00:00', 'label' => 'Morning Shift'],
        ['start' => '09:00:00', 'end' => '18:00:00', 'label' => 'Regular Shift'],
        ['start' => '10:00:00', 'end' => '19:00:00', 'label' => 'Late Shift'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Creating Departments...');
            $departments = $this->createDepartments();

            $this->command->info('Creating Admin...');
            $admin = $this->createAdmin();

            $this->command->info('Creating Staff, Schedules & Attendance...');
            foreach ($departments as $department) {
                $manager = User::factory()->create([
                    'name' => $department->name . ' Manager',
                    'email' => strtolower(str_replace([' ', '&'], '', $department->name)) . '_manager@example.com',
                    'role' => 'MANAGER',
                    'department_id' => $department->id,
                    'password' => Hash::make('password'),
                ]);

                $this->seedHistoryForUser($manager, $admin);

                $employees = User::factory(5)->create([
                    'role' => 'EMPLOYEE',
                    'department_id' => $department->id,
                    'password' => Hash::make('password'),
                ]);

                foreach ($employees as $employee) {
                    $this->seedLeaveRequests($employee);
                    $this->seedHistoryForUser($employee, $admin);
                }
            }
        });
        
        $this->command->info('Database seeded successfully with dynamic Schedules, Fines, and Attendance!');
    }

    private function createDepartments()
    {
        $deptNames = ['Human Resources', 'IT & Engineering', 'Finance', 'Marketing', 'Sales'];
        $models = [];
        foreach ($deptNames as $name) {
            $models[] = Department::create(['name' => $name]);
        }
        return $models;
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'role' => 'ADMIN',
            'password' => Hash::make('password'),
            'department_id' => null,
        ]);
    }

    private function seedLeaveRequests(User $user): void
    {
        LeaveRequest::factory(rand(1, 3))->create([
            'user_id' => $user->id,
            'status' => fake()->randomElement(['APPROVED', 'PENDING', 'REJECTED']),
            'start_date' => Carbon::now()->addDays(rand(-20, 20)),
            'end_date' => fn (array $attributes) => Carbon::parse($attributes['start_date'])->addDays(rand(1, 3)),
        ]);
    }

    private function seedHistoryForUser(User $user, User $admin): void
    {
        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now()->subDay(); // Up to yesterday
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $isSunday = $date->isSunday();
            
            $shiftPattern = self::SHIFTS[array_rand(self::SHIFTS)];

            $schedule = Schedule::create([
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'start_time' => $isSunday ? '00:00:00' : $shiftPattern['start'],
                'end_time' => $isSunday ? '00:00:00' : $shiftPattern['end'],
                'is_day_off' => $isSunday,
            ]);

            if ($schedule->is_day_off) {
                continue;
            }

            $dice = rand(1, 100);

            if ($dice <= 5) {
                $this->createAbsentRecord($user, $schedule);
            } elseif ($dice <= 20) {
                $this->createLateRecord($user, $admin, $schedule);
            } else {
                $this->createOnTimeRecord($user, $schedule);
            }
        }
    }

    private function createAbsentRecord(User $user, Schedule $schedule): void
    {
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $schedule->date->format('Y-m-d'),
            'status' => 'ABSENT',
            'clock_in_time' => null,
            'clock_out_time' => null,
            'late_minutes' => 0,
            'notes' => 'Unexcused absence',
        ]);

        Fine::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'amount' => 100000,
            'reason' => 'Unexcused Absence on ' . $schedule->date->format('d M Y'),
        ]);
    }

    private function createLateRecord(User $user, User $admin, Schedule $schedule): void
    {
        $workStart = Carbon::parse($schedule->date->format('Y-m-d') . ' ' . $schedule->start_time);
        
        $clockIn = (clone $workStart)->addMinutes(rand(5, 90));
        
        $lateMinutes = $workStart->diffInMinutes($clockIn, false);
        
        $clockOut = (clone $clockIn)->addHours(9)->addMinutes(rand(0, 30));

        $isForgiven = rand(1, 100) <= 20;

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $schedule->date->format('Y-m-d'),
            'status' => 'LATE',
            'clock_in_time' => $clockIn->format('H:i:s'),
            'clock_out_time' => $clockOut->format('H:i:s'),
            'late_minutes' => $lateMinutes,
            'ip_address' => fake()->ipv4(),
            'notes' => 'Arrived late for ' . $schedule->start_time . ' shift.',
            'is_forgiven' => $isForgiven,
            'forgiven_by' => $isForgiven ? $admin->id : null,
            'forgive_reason' => $isForgiven ? fake()->sentence(3) : null,
        ]);

        if (! $isForgiven && $lateMinutes > self::FINE_THRESHOLD_MINUTES) {
            Fine::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'amount' => 50000,
                'reason' => "Late arrival ({$lateMinutes} mins) exceeded threshold.",
            ]);
        }
    }

    private function createOnTimeRecord(User $user, Schedule $schedule): void
    {
        $workStart = Carbon::parse($schedule->date->format('Y-m-d') . ' ' . $schedule->start_time);
        
        $clockIn = (clone $workStart)->subMinutes(rand(0, 30));
        
        $clockOut = (clone $clockIn)->addHours(9)->addMinutes(rand(0, 30));

        Attendance::create([
            'user_id' => $user->id,
            'date' => $schedule->date->format('Y-m-d'),
            'status' => 'PRESENT',
            'clock_in_time' => $clockIn->format('H:i:s'),
            'clock_out_time' => $clockOut->format('H:i:s'),
            'late_minutes' => 0,
            'ip_address' => fake()->ipv4(),
            'notes' => null,
            'is_forgiven' => false,
        ]);
    }
}