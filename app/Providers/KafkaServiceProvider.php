<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\KafkaProducer;

class KafkaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            KafkaProducer::class,
            function ($app) {
                $config = \Kafka\ProducerConfig::getInstance();
                $config->setMetadataRefreshIntervalMs(10000);

                $config->setMetadataBrokerList(join(", ", config('kafka.brokers')));
                $config->setBrokerVersion("0.10.0.0");
                $config->setRequiredAck(1);
                $config->setIsAsync(false);
                $config->setClientId(config('kafka.client_id'));

                $config->setSecurityProtocol(\Kafka\Config::SECURITY_PROTOCOL_SASL_SSL);
                $config->setSaslMechanism(\Kafka\Config::SASL_MECHANISMS_PLAIN);
                $config->setSaslUsername(config('kafka.sasl.username'));
                $config->setSaslPassword(config('kafka.sasl.password'));

                $config->setSslEnable(true);
                $config->setSslEnableAuthentication(false);
                $config->setSslCafile(config('kafka.ca_file'));
                $config->setTimeout(6000);

                $producer = new \Kafka\Producer();

                return new KafkaProducer($producer);
            }
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function provides()
    {
        return [KafkaProducer::class];
    }
}
