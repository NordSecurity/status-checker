# Status checker

## Description
A library that tells you if any external service your
app relies on is broken.

Extremely useful for:
* Creating a command that will show whether the app is ok after deployment 
* Creating a status page for your system admins or monitoring software

## Requirements
- PHP >=7.4 || >=8.0

## Usage

### CLI

1. Install the library

```
composer require nordsec/status-checker
```

2. Declare an instance for StatusCheckerService (preferably using a container), pass various status checkers that make sense for your project

```PHP
$container[StatusCheckerService::class] = function (Container $container) {
    $configuration = $container['config'];

    return new StatusCheckerService([
        new DatabaseChecker('database.default_connection', $configuration['database']['default']),
        new RabbitMqChecker('rabbitmq.amqp_server', $configuration['queue']['connection_nordvpn_core']),
    ]);
};
```

3. If needed, register the command with your application
 
```PHP
$container[StatusCheckCommand::class] = function (Container $container) {
    return new StatusCheckCommand([
        $container[StatusCheckerService::class],
    ]);
};

$container[ConsoleApplication::class] = $container->extend(
    ConsoleApplication::class,
    function (ConsoleApplication $consoleApplication, Container $container) {
        $consoleApplication->add($container[StatusCheckCommand::class]);

        return $consoleApplication;
    }
);
```

4. Run the command

```
bin/console status:check
```

### WEB

1. Install the library

```
composer require nordsec/status-checker
```

2. Declare an instance for StatusCheckerService (preferably using a container), pass various status checkers that make sense for your project

```PHP
$container[StatusCheckerService::class] = function (Container $container) {
    $configuration = $container['config'];

    return new StatusCheckerService([
        new DatabaseChecker('database.default_connection', $configuration['database']['default']),
        new RabbitMqChecker('rabbitmq.nordvpn_core', $configuration['queue']['connection_nordvpn_core']),
    ]);
};
```

3. Create an instance of your controller (preferably using a container)

```
class StatusControllerProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container[StatusController::class] = function (Container $container) {
            $configuration = $container['config'];
            return new StatusController(
                $container[StatusCheckerService::class],
            );
        };
    }
}
```

4. Add needed routes
 
```
$app->get('/status', StatusController::class . ':index');
$app->get('/status/details', StatusController::class . ':details');
```

5. Access the above routes via your browser

* `/status` produces the overall (global) status 
    * if all services produce an `OK` status it will output `{"status":"OK"}`
    * if any service fails it will output `{"status":"FAIL"}`
    * if any service is in maintenance it will output `{"status":"MAINTENANCE"}`
* `/status/details` produces more detailed output about the status of every individual service

```
{"database.default":"OK","database.other":"OK"}
```
```
{"database.default":"OK","database.other":"FAIL"}
```
