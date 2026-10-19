<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixFlowDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $company = Company::where(
                'slug',
                'fixflow-demo'
            )->firstOrFail();

            $this->command?->info(
                'Preparing FixFlow Demo data...'
            );

            $manager = $this->createTeamMember(
                $company,
                'Demo Property Manager',
                'manager@demo.fixflow.test',
                'manager'
            );

            $technicians = [
                $this->createTeamMember(
                    $company,
                    'Alex Morgan',
                    'alex@demo.fixflow.test',
                    'technician'
                ),
                $this->createTeamMember(
                    $company,
                    'Jordan Lee',
                    'jordan@demo.fixflow.test',
                    'technician'
                ),
                $this->createTeamMember(
                    $company,
                    'Taylor Brooks',
                    'taylor@demo.fixflow.test',
                    'technician'
                ),
            ];

            $propertyData = [
                [
                    'name' => 'Harbor View Residences',
                    'address' => '100 Demo Harbor Lane',
                    'city' => 'Brooklyn',
                    'state' => 'NY',
                    'zip_code' => '00000',
                ],
                [
                    'name' => 'Maple Grove Apartments',
                    'address' => '200 Example Maple Street',
                    'city' => 'Queens',
                    'state' => 'NY',
                    'zip_code' => '00000',
                ],
                [
                    'name' => 'Riverside Heights',
                    'address' => '300 Sample Riverside Avenue',
                    'city' => 'Bronx',
                    'state' => 'NY',
                    'zip_code' => '00000',
                ],
            ];

            $units = [];

            foreach ($propertyData as $propertyIndex => $data) {
                $property = Property::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $data['name'],
                    ],
                    [
                        'address' => $data['address'],
                        'city' => $data['city'],
                        'state' => $data['state'],
                        'zip_code' => $data['zip_code'],
                    ]
                );

                for ($number = 1; $number <= 5; $number++) {
                    $floor = (int) ceil($number / 2);

                    $unitNumber = (string) (
                        ($floor * 100) + $number
                    );

                    $units[] = Unit::firstOrCreate(
                        [
                            'property_id' => $property->id,
                            'unit_number' => $unitNumber,
                        ],
                        [
                            'floor' => $floor,
                        ]
                    );
                }
            }

            $issues = [
                ['Leaking kitchen faucet', 'medium', 'open'],
                ['Air conditioning not cooling', 'high', 'assigned'],
                ['Hallway light flickering', 'low', 'completed'],
                ['Water heater leaking', 'emergency', 'in_progress'],
                ['Bathroom exhaust fan broken', 'medium', 'open'],
                ['Bedroom window will not close', 'high', 'assigned'],
                ['Dishwasher not draining', 'medium', 'completed'],
                ['Smoke detector chirping', 'high', 'in_progress'],
                ['Closet door off track', 'low', 'open'],
                ['Toilet continuously running', 'medium', 'assigned'],
                ['Refrigerator not cooling', 'high', 'in_progress'],
                ['Loose cabinet hinges', 'low', 'completed'],
                ['Heating system not responding', 'emergency', 'assigned'],
                ['Bathroom sink clogged', 'medium', 'open'],
                ['Broken entry door lock', 'high', 'completed'],
                ['Ceiling water stain', 'high', 'in_progress'],
                ['Washing machine vibrating', 'medium', 'assigned'],
                ['Damaged bedroom outlet', 'high', 'open'],
                ['Balcony door sticking', 'low', 'completed'],
                ['Water pressure unusually low', 'medium', 'assigned'],
            ];

            foreach ($issues as $index => $issue) {
                [$title, $priority, $status] = $issue;

                $unit = $units[$index % count($units)];

                $technician = $technicians[
                    $index % count($technicians)
                ];

                $assignedTo = $status === 'open'
                    ? null
                    : $technician->id;

                $scheduledStart = null;
                $scheduledEnd = null;

                if (in_array(
                    $status,
                    ['assigned', 'in_progress'],
                    true
                )) {
                    $scheduledStart = now()
                        ->addDays(($index % 12) + 1)
                        ->setTime(9 + ($index % 6), 0);

                    $scheduledEnd = $scheduledStart
                        ->copy()
                        ->addHours(2);
                }

                $request = MaintenanceRequest::firstOrCreate(
                    [
                        'unit_id' => $unit->id,
                        'title' => $title,
                    ],
                    [
                        'description' =>
                            'Fictional maintenance request created '
                            . 'for the FixFlow portfolio demonstration.',
                        'priority' => $priority,
                        'status' => $status,
                        'assigned_to' => $assignedTo,
                        'work_notes' =>
                            'Demo work order. No real tenant information.',
                        'scheduled_start_at' => $scheduledStart,
                        'scheduled_end_at' => $scheduledEnd,
                        'scheduling_notes' =>
                            $scheduledStart
                                ? 'Demo appointment.'
                                : null,
                    ]
                );

                if ($request->wasRecentlyCreated) {
                    $request->forceFill([
                        'created_at' => now()->subDays(
                            20 - ($index % 20)
                        ),
                    ])->save();
                }
            }

            $this->command?->info(
                'FixFlow Demo seeding completed.'
            );

            $this->command?->table(
                ['Resource', 'Count'],
                [
                    [
                        'Demo properties',
                        Property::where(
                            'company_id',
                            $company->id
                        )->count(),
                    ],
                    [
                        'Demo units',
                        Unit::whereHas(
                            'property',
                            fn ($query) => $query->where(
                                'company_id',
                                $company->id
                            )
                        )->count(),
                    ],
                    [
                        'Demo work orders',
                        MaintenanceRequest::whereHas(
                            'unit.property',
                            fn ($query) => $query->where(
                                'company_id',
                                $company->id
                            )
                        )->count(),
                    ],
                    [
                        'Active team members',
                        CompanyMembership::where(
                            'company_id',
                            $company->id
                        )->where('status', 'active')->count(),
                    ],
                ]
            );
        });
    }

    private function createTeamMember(
        Company $company,
        string $name,
        string $email,
        string $role
    ): User {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Str::random(64),
            ]
        );

        CompanyMembership::firstOrCreate(
            [
                'company_id' => $company->id,
                'user_id' => $user->id,
            ],
            [
                'role' => $role,
                'status' => 'active',
            ]
        );

        return $user;
    }
}
