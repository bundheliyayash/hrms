<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\ClientSite;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClientSiteSeeder extends Seeder
{
    public function run(): void
    {
        $clientsData = [
            [
                'name' => 'Tata Technology Center',
                'industry' => 'IT Services',
                'sites' => [
                    ['site_name' => 'Hinjewadi Phase 1', 'latitude' => '18.5913', 'longitude' => '73.7389'],
                    ['site_name' => 'Magarpatta City Tower B', 'latitude' => '18.5190', 'longitude' => '73.9240'],
                ]
            ],
            [
                'name' => 'Reliance Retail Hub',
                'industry' => 'Retail',
                'sites' => [
                    ['site_name' => 'Navi Mumbai Distribution Center', 'latitude' => '19.0330', 'longitude' => '73.0297'],
                    ['site_name' => 'Ghansoli Corporate Park', 'latitude' => '19.1232', 'longitude' => '73.0012'],
                ]
            ],
            [
                'name' => 'HDFC Bank Regional Office',
                'industry' => 'Banking',
                'sites' => [
                    ['site_name' => 'Worli HQ', 'latitude' => '18.9986', 'longitude' => '72.8174'],
                    ['site_name' => 'Kanjurmarg Branch', 'latitude' => '19.1314', 'longitude' => '72.9354'],
                ]
            ],
            [
                'name' => 'DLF Cyber City',
                'industry' => 'Real Estate',
                'sites' => [
                    ['site_name' => 'Building 10A Gurgaon', 'latitude' => '28.4950', 'longitude' => '77.0890'],
                    ['site_name' => 'Building 5C Gurgaon', 'latitude' => '28.4940', 'longitude' => '77.0880'],
                ]
            ],
            [
                'name' => 'Infosys Development Center',
                'industry' => 'IT',
                'sites' => [
                    ['site_name' => 'Electronic City Phase 1', 'latitude' => '12.8452', 'longitude' => '77.6633'],
                    ['site_name' => 'Mysuru Campus Gate 2', 'latitude' => '12.3023', 'longitude' => '76.6394'],
                ]
            ],
            [
                'name' => 'Mumbai International Airport (CSMIA)',
                'industry' => 'Aviation',
                'sites' => [
                    ['site_name' => 'Terminal 2 Departure', 'latitude' => '19.0896', 'longitude' => '72.8656'],
                    ['site_name' => 'T1 Ground Maintenance', 'latitude' => '19.0974', 'longitude' => '72.8514'],
                ]
            ],
            [
                'name' => 'Max Healthcare',
                'industry' => 'Healthcare',
                'sites' => [
                    ['site_name' => 'Saket Hospital Wing A', 'latitude' => '28.5280', 'longitude' => '77.2195'],
                ]
            ],
            [
                'name' => 'Marriott International',
                'industry' => 'Hospitality',
                'sites' => [
                    ['site_name' => 'JW Marriott Juhu', 'latitude' => '19.1022', 'longitude' => '72.8258'],
                    ['site_name' => 'Sahar Airport Hotel', 'latitude' => '19.1017', 'longitude' => '72.8752'],
                ]
            ],
            [
                'name' => 'Amazon Fulfillment Center',
                'industry' => 'Logistics',
                'sites' => [
                    ['site_name' => 'Bhiwandi FC 1', 'latitude' => '19.2967', 'longitude' => '73.0597'],
                ]
            ],
            [
                'name' => 'Godrej Properties',
                'industry' => 'Real Estate',
                'sites' => [
                    ['site_name' => 'Godrej One Vikhroli', 'latitude' => '19.1030', 'longitude' => '72.9250'],
                ]
            ],
        ];

        foreach ($clientsData as $cData) {
            $client = Client::updateOrCreate(['name' => $cData['name']], [
                'email' => strtolower(str_replace(' ', '.', $cData['name'])) . '@demo.com',
                'phone' => '+91 9' . rand(0, 9) . rand(0, 9) . rand(1000000, 9999999),
                'address' => 'Corporate Office, ' . $cData['sites'][0]['site_name'],
                'is_active' => true,
                'service_start_date' => Carbon::now()->subYears(2),
            ]);

            foreach ($cData['sites'] as $sData) {
                $site = ClientSite::updateOrCreate(['site_name' => $sData['site_name'], 'client_id' => $client->id], [
                    'latitude' => $sData['latitude'],
                    'longitude' => $sData['longitude'],
                    'radius_meters' => 200,
                    'is_active' => true,
                ]);

                // Create a contract for the site (Many-to-Many logic)
                $contract = Contract::updateOrCreate([
                    'contract_number' => 'CON-' . strtoupper(substr(md5($sData['site_name']), 0, 8))
                ], [
                    'client_id' => $client->id,
                    'contract_type' => 'permanent',
                    'billing_type' => 'per_month',
                    'rate_per_day' => rand(2000, 5000), // In per_month mode, this represents daily base for calc
                    'start_date' => Carbon::now()->subYears(1),
                    'end_date' => Carbon::now()->addYears(1),
                    'status' => 'active',
                ]);

                // Link contract to site
                DB::table('contract_sites')->updateOrInsert([
                    'contract_id' => $contract->id,
                    'site_id' => $site->id,
                ], [
                    'workers_required' => rand(5, 15),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
        }
    }
}
