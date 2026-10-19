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
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class FixFlowDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Never allow this seeder to run on the main database.
        $database = DB::connection()->getDatabaseName();

        if ($database !== 'fixflow_demo') {
            throw new RuntimeException(
                "Demo seeder blocked. Connected database: {$database}"
            );
        }

        DB::transaction(function () {

            // ----------------------------------------
            // 1. Demo company
            // ----------------------------------------

            $company = Company::firstOrCreate(
                [
                    'slug' => 'fixflow-demo',
                ],
                [
                    'name' => 'FixFlow Demo Property Management',
                    'email' => 'demo@fixflow.test',
                    'status' => 'active',
                ]
            );

            // ----------------------------------------
            // 2. Demo accounts
            // ----------------------------------------

            $accounts = [
                [
                    'name' => 'Demo Administrator',
                    'email' => 'admin.demo@fixflow.test',
                    'role' => 'company_admin',
                ],
                [
                    'name' => 'Demo Manager',
                    'email' => 'manager.demo@fixflow.test',
                    'role' => 'manager',
                ],
                [
                    'name' => 'Jordan Williams',
                    'email' => 'technician1.demo@fixflow.test',
                    'role' => 'technician',
                ],
                [
                    'name' => 'Taylor Morgan',
                    'email' => 'technician2.demo@fixflow.test',
                    'role' => 'technician',
                ],
            ];

            $users = [];

            foreach ($accounts as $account) {

                $user = User::firstOrCreate(
                    [
                        'email' => $account['email'],
                    ],
                    [
                        'name' => $account['name'],
                        'password' => Hash::make('FixFlowDemo2026!'),
                        'email_verified_at' => now(),
                    ]
                );

                CompanyMembership::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'role' => $account['role'],
                        'status' => 'active',
                    ]
                );

                $users[$account['email']] = $user;
            }

            $technician1 = $users['technician1.demo@fixflow.test'];
            $technician2 = $users['technician2.demo@fixflow.test'];

            // ----------------------------------------
            // 3. Demo properties and apartments
            // ----------------------------------------

            $properties = [
                [
                    'name' => 'Harbor Point',
                    'address' => '100 Demo Harbor Avenue',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip_code' => '10001',
                    'units' => [
                        '225',
                        '325',
                        '425',
                        '1222',
                    ],
                ],
                [
                    'name' => 'The Vault',
                    'address' => '200 Demo Vault Street',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip_code' => '10002',
                    'units' => [
                        '101',
                        '220',
                        '310',
                        '411',
                    ],
                ],
                [
                    'name' => 'The Key',
                    'address' => '300 Demo Key Boulevard',
                    'city' => 'New York',
                    'state' => 'NY',
                    'zip_code' => '10003',
                    'units' => [
                        '107',
                        '312',
                        '421',
                        '512',
                    ],
                ],
            ];

            $units = [];

            foreach ($properties as $propertyData) {

                $property = Property::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $propertyData['name'],
                    ],
                    [
                        'address' => $propertyData['address'],
                        'city' => $propertyData['city'],
                        'state' => $propertyData['state'],
                        'zip_code' => $propertyData['zip_code'],
                    ]
                );

                foreach ($propertyData['units'] as $unitNumber) {

                    $floor = strlen($unitNumber) > 2
                        ? (int) substr($unitNumber, 0, -2)
                        : 1;

                    $unit = Unit::firstOrCreate(
                        [
                            'property_id' => $property->id,
                            'unit_number' => $unitNumber,
                        ],
                        [
                            'floor' => $floor,
                        ]
                    );

                    $units[
                        $propertyData['name'] . ':' . $unitNumber
                    ] = $unit;
                }
            }

            // ----------------------------------------
            // 4. Demo maintenance requests
            // ----------------------------------------

            $jobs = [
                [
                    'unit' => 'Harbor Point:425',
                    'title' => 'Water heater leaking',
                    'description' => 'Water heater leaking into nearby hallway.',
                    'priority' => 'emergency',
                    'status' => 'open',
                    'technician' => null,
                ],
                [
                    'unit' => 'Harbor Point:325',
                    'title' => 'Water damage inspection',
                    'description' => 'Inspect hallway and flooring for water damage.',
                    'priority' => 'high',
                    'status' => 'assigned',
                    'technician' => $technician1,
                ],
                [
                    'unit' => 'Harbor Point:225',
                    'title' => 'Ceiling moisture',
                    'description' => 'Check ceiling after reported leak from above.',
                    'priority' => 'high',
                    'status' => 'in_progress',
                    'technician' => $technician2,
                ],
                [
                    'unit' => 'Harbor Point:1222',
                    'title' => 'Bathroom plumbing repair',
                    'description' => 'Inspect leaking bathroom supply connection.',
                    'priority' => 'medium',
                    'status' => 'completed',
                    'technician' => $technician1,
                ],
                [
                    'unit' => 'The Vault:101',
                    'title' => 'Kitchen faucet repair',
                    'description' => 'Kitchen faucet dripping continuously.',
                    'priority' => 'low',
                    'status' => 'completed',
                    'technician' => $technician2,
                ],
                [
                    'unit' => 'The Vault:220',
                    'title' => 'Access fob not working',
                    'description' => 'Resident cannot access building using issued fob.',
                    'priority' => 'medium',
                    'status' => 'completed',
                    'technician' => $technician1,
                ],
                [
                    'unit' => 'The Vault:310',
                    'title' => 'Smoke detector inspection',
                    'description' => 'Smoke detector chirping intermittently.',
                    'priority' => 'high',
                    'status' => 'assigned',
                    'technician' => $technician2,
                ],
                [
                    'unit' => 'The Vault:411',
                    'title' => 'Bedroom outlet repair',
                    'description' => 'Outlet is not supplying power.',
                    'priority' => 'medium',
                    'status' => 'open',
                    'technician' => null,
                ],
                [
                    'unit' => 'The Key:107',
                    'title' => 'Laundry area leak',
                    'description' => 'Water found behind washer and dryer.',
                    'priority' => 'high',
                    'status' => 'in_progress',
                    'technician' => $technician1,
                ],
                [
                    'unit' => 'The Key:312',
                    'title' => 'Apartment lockout',
                    'description' => 'Resident requires assistance with apartment access.',
                    'priority' => 'medium',
                    'status' => 'completed',
                    'technician' => $technician2,
                ],
                [
                    'unit' => 'The Key:421',
                    'title' => 'Air conditioning failure',
                    'description' => 'Cooling system not functioning; temporary unit needed.',
                    'priority' => 'high',
                    'status' => 'assigned',
                    'technician' => $technician1,
                ],
                [
                    'unit' => 'The Key:512',
                    'title' => 'Window latch repair',
                    'description' => 'Window latch does not close properly.',
                    'priority' => 'low',
                    'status' => 'open',
                    'technician' => null,
                ],
            ];

            foreach ($jobs as $job) {

                MaintenanceRequest::firstOrCreate(
                    [
                        'unit_id' => $units[$job['unit']]->id,
                        'title' => $job['title'],
                    ],
                    [
                        'description' => $job['description'],
                        'priority' => $job['priority'],
                        'status' => $job['status'],
                        'assigned_to' => $job['technician']?->id,
                    ]
                );
            }
        });

        $this->command->info(
            'FixFlow demo data created successfully!'
        );
    }
}
