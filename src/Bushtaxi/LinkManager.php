<?php

namespace Bushtaxi;

class LinkManager
{
    private $links = [];

    public function __construct($config, $log)
    {
        $this->config = $config;
        $this->log = $log;
        $this->context = new \ZMQContext();
    }

    public function connect()
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

    public function getLinks()
    {
        return $this->links;
    }

    public function getLink($name)
    {
        return $this->links[$name];
    }


    private function handle_xsub($name, $link, $config)
    {
        $this->log->debug("handle_xsub to $name");
        $this->handle_bind($name, $link, $config);
        $this->log->debug("subscribe to everything");
        $link->send(chr(1));
        $this->log->debug("done");
    }


    private function handle_xpub($name, $link, $config)
    {
        $this->log->debug("handle_xpub to $name");
        $this->handle_bind($name, $link, $config);
    }

    private function handle_sub($name, $link, $config)
    {
        $this->log->debug("handle_sub to $name");
        $this->handle_connect($name, $link, $config);
        $this->handle_subscriptions($link, $config);
    }

    private function handle_bind($name, $link, $config)
    {
        $this->log->debug("handle_bind");

        if (is_string($config['bind'])) {
            $config['bind'] = [$config['bind']];
        }

        foreach ($config['bind'] as $bindAddress) {
            $this->log->debug("binding $name to $bindAddress");
            $link->bind($bindAddress);
            $this->log->debug("bound $name to $bindAddress");
        }
    }

    private function handle_connect($name, $link, $config)
    {
        $this->log->debug("handle_connect");

        $this->log->debug("connect $name to ${config['connect']}");
        $link->connect($config['connect']);
        $this->log->debug("connected $name to ${config['connect']}");
    }

    private function handle_rep($name, $link, $config)
    {
        $this->log->debug("handle_rep to $name");
        $this->handle_bind_or_connect($name, $link, $config);
    }

    private function handle_pub($name, $link, $config)
    {
        $this->log->debug("handle_pub from $name");
        $this->handle_bind_or_connect($name, $link, $config);
    }

    private function handle_req($name, $link, $config)
    {
        $this->log->debug("handle_req to $name");
        $this->handle_connect($name, $link, $config);
    }

    private function handle_push($name, $link, $config)
    {
        $this->log->debug("handle_push from $name");
        $this->handle_bind_or_connect($name, $link, $config);
    }

    private function handle_router($name, $link, $config)
    {
        $this->log->debug("handle_router from $name");
        $this->handle_bind($name, $link, $config);
    }

    private function handle_dealer($name, $link, $config)
    {
        $this->log->debug("handle_dealer from $name");
        $this->handle_bind($name, $link, $config);
    }

    private function handle_pull($name, $link, $config)
    {
        $this->log->debug("handle_pull from $name");
        $this->handle_bind_or_connect($name, $link, $config);
    }

    private function handle_bind_or_connect($name, $link, $config)
    {
        if (isset($config['connect'])) {
            // connect to xsub
            $this->handle_connect($name, $link, $config);
        } elseif (isset($config['bind'])) {
            // bind to sub
            $this->handle_bind($name, $link, $config);
        }
    }

    private function handle_subscriptions($link, $config)
    {
        foreach ($config['subscriptions'] as $subscription => $endpoint) {
            $this->log->debug("subscribe to $subscription");
            $link->setSockOpt(\ZMQ::SOCKOPT_SUBSCRIBE, $subscription);
        }
    }
}
