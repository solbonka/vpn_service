<?php

namespace Database\Seeders;

use App\Models\Server;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    public function run(): void
    {
        $mainServers = [
            [
                'base_url'       => 'https://riga-main.domain.online',
                'login'          => 'login',
                'password'       => 'password',
                'host'           => '5.111.222.333',
                'name'           => '🇱🇻 Instagram, TikTok, Chatgpt и др.',
                'code'           => 'riga-main',
                'subdomain'      => 'riga-main.domain.online',
                'subdomain_node' => 'riga-node.domain.online',
                'is_active'      => true,
                'order'          => 3
            ],
            [
                'base_url'       => 'https://main-germ.domain.online',
                'login'          => 'login',
                'password'       => 'password',
                'host'           => '5.111.222.333',
                'name'           => '🇩🇪 Instagram, Chatgpt и др.',
                'code'           => 'main-germ',
                'subdomain'      => 'main-germ.domain.online',
                'subdomain_node' => 'germ-node.domain.online',
                'is_active'      => true,
                'order'          => 2
            ],
            [
                'base_url'       => 'https://ru-spb-main.domain.online',
                'login'          => 'login',
                'password'       => 'password',
                'host'           => '5.111.222.333',
                'name'           => '🇷🇺 Youtube без рекламы, Instagram',
                'code'           => 'ru-spb-main',
                'subdomain'      => 'ru-spb-main.domain.online',
                'subdomain_node' => 'ru-spb-node.domain.online',
                'is_active'      => true,
                'order'          => 4
            ],
            [
                'base_url'       => 'https://nl-amst-main.domain.online',
                'login'          => 'login',
                'password'       => 'password',
                'host'           => '5.111.222.333',
                'name'           => '🇳🇱 Instagram, TikTok, Chatgpt и др.',
                'code'           => 'nl-amst-main',
                'subdomain'      => 'nl-amst-main.domain.online',
                'subdomain_node' => 'nl-amst-node.domain.online',
                'is_active'      => true,
                'order'          => 1
            ],
        ];

        foreach ($mainServers as $server) {
            Server::firstOrCreate(
                ['host' => $server['host']],
                $server
            );
        }
    }
}
