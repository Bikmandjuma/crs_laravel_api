<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\SensorReading;

class MqttSubscribe extends Command
{

    protected $signature = 'mqtt:subscribe';

    protected $description = 'Subscribe ESP32 MQTT sensor data';


    public function handle()
    {

        $mqtt = new MqttClient(
            config('mqtt.host'),
            config('mqtt.port'),
            'laravel-subscriber'
        );


        $settings = (new ConnectionSettings)
            ->setKeepAliveInterval(60);


        $mqtt->connect($settings,true);


        $this->info("MQTT Connected");


        $mqtt->subscribe(
            config('mqtt.topic'),
            function($topic,$message)
            {

                echo "\nTopic: ".$topic;
                echo "\nData: ".$message;


                $data=json_decode($message,true);


                SensorReading::create([

                    // 'device_id'=>$data['device_id'] ?? null,

                    'soil_moisture'=>$data['soil']['soil_moisture'] ?? 0,

                    'pH'=>$data['soil']['pH'] ?? 0,

                    'temperature'=>$data['soil']['temperature'] ?? 0,

                    'conductivity'=>$data['soil']['conductivity'] ?? 0,

                    'N'=>$data['soil']['N'] ?? 0,

                    'P'=>$data['soil']['P'] ?? 0,

                    'K'=>$data['soil']['K'] ?? 0,

                    'weather_temperature'=>$data['ambient']['weather_temperature'] ?? 0,

                    'weather_humidity'=>$data['ambient']['weather_humidity'] ?? 0,

                ]);


                echo "\nSaved to database\n";

            },

            0
        );


        $mqtt->loop(true);

    }
}