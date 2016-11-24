# Bushtaxi

Bushtaxi aims at making your life with ZeroMQ easier. It establishes connections and bindings based on simple configurations.

## Usage

### Client

A basic configuration for a req socket looks like this:

```
$config = [
  "service" => [ "name" => "client" ],
  "links" => [
    "server" => [
      "type" => "req",
      "connect" => "tcp://10.0.0.100:5000"
    ]
  ]
];
```

With this config you now can easily send a message to the socket specified in the config.

```
$bushtaxi = new Bushtaxi\Client($config);
$bushtaxi->server->send('Hello World');
```

### Server

Binding sockets and running servers is as easy. First create a config.

```
$config = [
  "service" => [ "name" => "server" ],
  "links" => [
    "client" => [
	  "type" => "rep",
      "bind" => "tcp://10.0.0.100:5000
    ]
  ]
];
```

In addition to the config you also need to specify a [server runtime class](./src/Bushtaxi/ServerRuntime.php). This runtime class determines what happens in every loop.

```
$runtime = new class extends Bushtaxi\AbstractServerRuntime {
    function handle($links) {
		$message = $links['client']->recv();
		$links['client']->send("World");
    }
};
```

And we are good to go:

```
$bushtaxi = new Bushtaxi\Server($config, $runtime);
$bushtaxi->run();
```

You can find this example in [the examples directory](./examples).

## Run tests

```
cd bushtaxi
for f in $(find tests/*.yml); do docker-compose -f $f up; done
```
