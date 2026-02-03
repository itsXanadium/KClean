<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MqttSubscribe extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'Subscribe HiveMQ and process sensor data';

    public function handle()
    {
        $mqtt = new MqttClient(
            config('mqtt.host'),
            config('mqtt.port'),
            config('mqtt.client_id')
        );

        $settings = (new ConnectionSettings)
            ->setUsername(config('mqtt.username'))
            ->setPassword(config('mqtt.password'))
            ->setUseTls(true)
            ->setTlsSelfSignedAllowed(true);

        $mqtt->connect($settings, true);

        $this->info('MQTT Connected');

        $mqtt->subscribe('sensor/timbangan/berat', function ($topic, $message) {
            $this->info("[$topic] $message");

            // Simpan ke database
            DB::table('weights')->insert([
                'value' => (float) $message,
                'created_at' => now(),
            ]);

        }, 0);

        // LOOP (JANGAN DIHAPUS)
        $mqtt->loop(true);
    }
}