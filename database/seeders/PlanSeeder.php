<?php
namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;


class PlanSeeder extends Seeder
{
    public function run(): void
    {

        Plan::create([
            'name'             => 'Basic',
            'slug'             => 'basic',
            'description'      => 'For small leagues getting started',
            'monthly_price'    => 299.00,
            'annual_price'     => 2988.00, // $249/mo
            'max_tournaments'  => 2,
            'max_teams'        => 20,
            'max_players'      => 300,
            'max_users'        => 2,
            'max_fields'       => 5,
            'max_referees'     => 10,
            'storage_mb'       => 500,
            'has_advanced_stats' => false,
            'has_reports'      => false,
            'support_level'    => 'basic',
            'sort_order'       => 1,
        ]);

        Plan::create([
            'name'             => 'Pro',
            'slug'             => 'pro',
            'description'      => 'For growing leagues that want more',
            'monthly_price'    => 799.00,
            'annual_price'     => 7788.00, // $649/mo
            'max_tournaments'  => 10,
            'max_teams'        => 60,
            'max_players'      => 1500,
            'max_users'        => 10,
            'max_fields'       => 20,
            'max_referees'     => 30,
            'storage_mb'       => 5000,
            'has_mobile_app'   => true,
            'has_advanced_stats' => true,
            'has_whatsapp'     => true,
            'has_reports'      => true,
            'support_level'    => 'priority',
            'sort_order'       => 2,
            'is_featured'      => true,
        ]);

        Plan::create([
            'name'             => 'Enterprise',
            'slug'             => 'enterprise',
            'description'      => 'Full control for large organizations',
            'monthly_price'    => 1999.00,
            'annual_price'     => 20388.00, // $1699/mo
            'max_tournaments'  => -1, // unlimited
            'max_teams'        => -1,
            'max_players'      => -1,
            'max_users'        => -1,
            'max_fields'       => -1,
            'max_referees'     => -1,
            'storage_mb'       => 50000,
            'has_mobile_app'   => true,
            'has_streaming'    => true,
            'has_advanced_stats' => true,
            'has_api_access'   => true,
            'has_whatsapp'     => true,
            'has_reports'      => true,
            'has_custom_domain' => true,
            'support_level'    => 'dedicated',
            'sort_order'       => 3,
        ]);
    }
}
