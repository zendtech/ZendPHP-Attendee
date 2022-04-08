# ZendPHP Session Clustering for Docker

Run `make` or `make help` for some command examples.

___

## What it is

This folder contains artifacts exemplifying PHP session clustering with Redis as the designated storage for sessions.

The implementation contains the following elements:

- nginx server operating as web server (for static resources) and load balancer.

- ZendPHP as the runtime for the PHP applications phpMyAdmin (an open source MySQL/MairaDB administration tool) and a simple script named "TestSC" (for testing sessions in a clustered environment).

- Redis server for storing applications sessions.

- A MariaDB server to support phpMyAdmin.

### Implementations

There are 3 implemented workflows for this example:

1. A simple Docker Compose implementation (`docker-compose.yml`) with no clustered PHP servers.
   It demonstrates how the various images are built, but mostly demonstrates the basic mechanics for build a full-fledged containerized PHP runtime with ZendPHP with session clustering.

2. A Docker Swarm (Stack) implementation (`docker-stack.yml`) with 3 clustered PHP servers behind an nginx instance acting as a load balancer in a round-robin fashion.

3. A Kubernetes (k8s) implementation(`k8s` folder) equivalent to the Docker Swarm implementation.

A `Makefile` is available to streamline starting, cleaning up, and querying the run status of each implementation mentioned above.

Please run `make` or `make help` in order to get a better idea of the available commands.
In particular, pay attention to the cleanup tasks which can be useful when you are done experimenting with this project.

## Usage

Fire up your chosen implementation:

- Compose: `make compose`
- Swarm/Stack: `make stack`
- Kubernetes: `make kube`

From there, browse to http://localhost:8080, where you will receive a login screen for phpMyAdmin.
Use the following values:

- Username: root
- Password: rootpw
- Host: db
- Port: 3306

You can check the status of the cluster using the following, which can sometimes be necessary to determine the correct URL to browse to:

- Compose and Swarm/Stack `make info-docker`
- Kubernetes: `make info-kube`

When done testing things out, run:

- Compose: `make clean-compose`
- Swarm/Stack: `make clean-stack`
- Kubernetes: `make clean-kube`

### Kubernetes

The `Makefile` assumes you are using `kubectl` to start and stop services.
If you are using a different command, e.g. `microk8s kubectl`, you can pass it in via the `KUBECTL` argument:

```bash
$ make kube KUBECTL="microk8s kubectl"
```

Note: you will also need to pass that argument when calling `make clean-kube` later.
You can eliminate the need to pass the argument to each `make` invocation by exporting it:

```bash
$ export KUBECTL="microk8s kubectl"
```

#### Microk8s

For microk8s, there are a few things you need to do to ensure that everything can orchestrate correctly.

First, enable DNS and ingress services:

```bash
$ microk8s enable dns ingress
```

Next, enable the metallb service:

```bash
$ microk8s enable metallb
```

This will prompt you for an address range.
The network range must be accessible via one of your adapters, whether it's virtual or physical.
If you are testing on your own machine, and do not need outside machines to have access, you might try something like `127.0.1.16-127.0.1.64`.

After this, you can fire up the cluster:

```bash
$ export KUBECTL="microk8s kubectl"
$ make kube
```

Wait a few seconds, and then run:

```bash
$ make info-kube
```

You will see first a list of pods, and then a list of services.
The web-server service will include the `EXTERNAL-IP` by which you can access; also make note of which ports are available (typically, 8080 and 8081).
