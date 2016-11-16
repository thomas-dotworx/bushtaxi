<?php

namespace Bushtaxi;

class Server
{
    private $links = [];

    public function __construct($config, $log, $runtime)
    {
        $this->config = $config;
        $this->log = $log;
        $this->runtime = $runtime;
    }

    public function run()
    {
        $this->init();
        $this->log->info("Bushtaxi for {$this->config['service']['name']} is ready");
        while ($this->runtime->isRunning()) {
            $this->runtime->handle($this->links);
        }
        $this->log->debug("exit");
    }

    private function init()
    {
        $this->context = new \ZMQContext();

        $this->log->debug("init()");
        $this->connect();

        $this->log->debug("call runtime init()");
        $this->runtime->init();

        $this->log->debug("done: init()");
    }

    private function connect()
    {
        foreach ($this->config['links'] as $name=>$config) {
            $this->log->debug("Establish {$config['type']} ZMQSocket for {$name}");
            $this->links[$name] = new \ZMQSocket(
                $this->context,
                constant(sprintf("\\ZMQ::SOCKET_%s", strtoupper($config['type'])))
            );

            if (method_exists($this, "handle_{$config['type']}")) {
                call_user_func(
                    [$this, "handle_{$config['type']}"],
                    $name,
                    $this->links[$name],
                    $config
                );
            }
        }
    }

    private function handle_sub($name, $link, $config)
    {
        $this->log->debug("handle_sub to $name");
        $link->connect("tcp://$name:5564");
        foreach ($config['subscriptions'] as $subscription=>$endpoint) {
            $this->log->debug("subscribe to $subscription");
            $link->setSockOpt(\ZMQ::SOCKOPT_SUBSCRIBE, $subscription);
        }
    }

    private function handle_rep($name, $link, $config)
    {
        $this->log->debug("handle_rep to $name");
        $link->bind("tcp://*:5564");
        $this->log->debug("bound to tcp://*:5564");
    }

    private function handle_pub($name, $link, $config)
    {
        $this->log->debug("handle_pub from $name");
        $link->bind("tcp://*:5564");
        $this->log->debug("bound to tcp://*:5564");
    }

    private function handle_req($name, $link, $config)
    {
        $this->log->debug("handle_req to $name");
        $link->connect("tcp://$name:5564");
        $this->log->debug("connected to tcp://$name:5564");
    }

    private function handle_push($name, $link, $config)
    {
        $this->log->debug("handle_push from $name");
        $link->bind("tcp://*:5564");
        $this->log->debug("bound to tcp://*:5564");
    }

    private function handle_pull($name, $link, $config)
    {
        $this->log->debug("handle_pull from $name");
        $link->connect("tcp://$name:5564");
        $this->log->debug("connected to tcp://$name:5564");
    }

    private function subscribe()
    {
        foreach ($this->config->subscriptions as $subscription=>$target) {
            $this->log->debug(sprintf("subscribing for %s", $subscription));
        }
    }
}