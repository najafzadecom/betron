<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'name' => 'Site Adı',
                'description' => 'CMS Panel Adı',
                'value' => 'CMS Panel',
                'type' => 'text',
                'group' => 'general',
            ],
            [
                'key' => 'site_description',
                'name' => 'Site Açıklaması',
                'description' => 'CMS Panel Açıklaması',
                'value' => 'İdarəetmə paneli',
                'type' => 'textarea',
                'group' => 'general',
            ],
            [
                'key' => 'site_logo',
                'name' => 'Site Logosu',
                'description' => 'CMS Panel Logosu',
                'value' => null,
                'type' => 'file',
                'group' => 'general',
            ],
            [
                'key' => 'maintenance_mode',
                'name' => 'Bakım Modu',
                'description' => 'Sitenin bakım modunda olup olmaması',
                'value' => false,
                'type' => 'boolean',
                'group' => 'general',
            ],

            // Email Settings
            [
                'key' => 'email_from_address',
                'name' => 'Gönderen Email',
                'description' => 'CMS Panelinden gönderilen emaillerin gönderen email adresi',
                'value' => 'noreply@example.com',
                'type' => 'email',
                'group' => 'email',
            ],
            [
                'key' => 'email_from_name',
                'name' => 'Gönderen Adı',
                'description' => 'CMS Panelinden gönderilen emaillerin gönderen adı',
                'value' => 'CMS Panel',
                'type' => 'text',
                'group' => 'email',
            ],

            // Payment Settings
            [
                'key' => 'payment_currency',
                'name' => 'Ödeme Para Birimi',
                'description' => 'CMS Panelinde kullanılan ödeme para birimi',
                'value' => 'AZN',
                'type' => 'text',
                'group' => 'payment',
            ],
            [
                'key' => 'min_withdrawal_amount',
                'name' => 'Minimum Çekim Tutarı',
                'description' => 'Kullanıcıların minimum çekim yapabileceği tutar',
                'value' => 10,
                'type' => 'number',
                'group' => 'payment',
            ],
            [
                'key' => 'max_withdrawal_amount',
                'name' => 'Maksimum Çekim Tutarı',
                'description' => 'Kullanıcıların maksimum çekim yapabileceği tutar',
                'value' => 10000,
                'type' => 'number',
                'group' => 'payment',
            ],

            // Security Settings
            [
                'key' => 'session_timeout',
                'name' => 'Oturum Süresi',
                'description' => 'Kullanıcı oturumunun zaman aşımı süresi (dakika)',
                'value' => 120,
                'type' => 'number',
                'group' => 'security',
            ],
            [
                'key' => 'max_login_attempts',
                'name' => 'Maksimum Giriş Denemesi',
                'description' => 'Kullanıcıların maksimum giriş deneme sayısı',
                'value' => 5,
                'type' => 'number',
                'group' => 'security',
            ],

            // API Settings
            [
                'key' => 'api_rate_limit',
                'name' => 'API Limiti',
                'description' => 'API için limit (saniyede)',
                'value' => 60,
                'type' => 'number',
                'group' => 'api',
            ],

            // System Settings
            [
                'key' => 'timezone',
                'name' => 'Zaman Dilimi',
                'description' => 'CMS Panelinin kullandığı zaman dilimi',
                'value' => 'Asia/Baku',
                'type' => 'text',
                'group' => 'system',
            ],
            [
                'key' => 'date_format',
                'name' => 'Tarih Formatı',
                'description' => 'CMS Panelinde kullanılan tarih formatı',
                'value' => 'Y-m-d H:i:s',
                'type' => 'text',
                'group' => 'system',
            ],
            [
                'key' => 'draft_status_interval',
                'name' => 'Transaction Draft Status Interval',
                'description' => 'Time in minutes after which transactions in Processing status will be changed back to Draft status',
                'value' => 15,
                'type' => 'number',
                'group' => 'system',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
