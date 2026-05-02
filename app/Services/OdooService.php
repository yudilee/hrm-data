<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Laminas\XmlRpc\Client;

class OdooService
{
    protected ?string $url;

    protected ?string $db;

    protected ?string $user;

    protected ?string $password;

    public function __construct()
    {
        $config = Setting::getOdooConfig();
        $this->url = $config['url'] ?: null;
        $this->db = $config['db'] ?: null;
        $this->user = $config['user'] ?: null;
        $this->password = $config['password'] ?: null;
    }

    public function testConnection(): array
    {
        if (! $this->url || ! $this->db || ! $this->user || ! $this->password) {
            return [
                'success' => false,
                'message' => 'Odoo configuration is incomplete. Please configure URL, database, username, and password.',
            ];
        }

        try {
            $client = new Client($this->url.'/xmlrpc/2/common');

            $result = $client->call('authenticate', [
                $this->db,
                $this->user,
                $this->password,
                [],
            ]);

            if ($result && is_numeric($result)) {
                return [
                    'success' => true,
                    'message' => 'Successfully connected to Odoo. User ID: '.$result,
                ];
            }

            return [
                'success' => false,
                'message' => 'Authentication failed. Check your Odoo credentials.',
            ];

        } catch (\Exception $e) {
            Log::error('Odoo connection test failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ];
        }
    }
}
