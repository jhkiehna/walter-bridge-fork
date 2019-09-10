<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\KafkaProducer;
use App\Services\KafkaConsumer;

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

                $this->setConfigConnection($config);

                $config->setClientId(config('kafka.client_id'));
                $config->setRequiredAck(1);
                $config->setIsAsync(false);

                $producer = new \Kafka\Producer();

                return new KafkaProducer($producer);
            }
        );

        $this->app->singleton(
            KafkaConsumer::class,
            function ($app) {
                $config = \Kafka\ConsumerConfig::getInstance();

                $this->setConfigConnection($config);

                $config->setGroupId("walter-bridge");
                $config->setTopics(config('kafka.topics'));
                $config->setOffsetReset("earliest");

                $consumer = new \Kafka\Consumer();

                return new KafkaConsumer($consumer);
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
        return [KafkaProducer::class, KafkaConsumer::class];
    }

    protected function setConfigConnection($config)
    {
        $config->setMetadataBrokerList($this->brokers());
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setBrokerVersion(config("kafka.broker_version"));

        $config->setSecurityProtocol(\Kafka\Config::SECURITY_PROTOCOL_SASL_SSL);
        $config->setSaslMechanism(\Kafka\Config::SASL_MECHANISMS_PLAIN);
        $config->setSaslUsername(config('kafka.sasl.username'));
        $config->setSaslPassword(config('kafka.sasl.password'));

        $config->setSslEnable(true);
        $config->setSslEnableAuthentication(false);
        $config->setSslCafile(config('kafka.ca_file'));
        $config->setTimeout(7000);
    }

    /**
     * Returns comma seperated list of brokers.
     *
     * @return string
     */
    protected function brokers()
    {
        return join(", ", config('kafka.brokers'));
    }
}
