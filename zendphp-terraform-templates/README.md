# Terraform orchestration for ZendPHP

This archive provides a sample Terraform template for creating a session cluster on AWS using ZendPHP.

## Prerequisites

The samples assume you are running on a Unix-like environment such as Linux, MacOS, or the Windows Subsystem for Linux.

- You will need the `make` utility; see your operating system documentation to determine how to install this utility.
- You will need `ssh`; see your operating system documentation to determine how to install this utility.
- You will need the [AWS CLI tool](https://aws.amazon.com/cli/); see the [AWS documentation](https://docs.aws.amazon.com/cli/latest/userguide/install-cliv2.html) for details on how to install the tool.
  Make sure you have run `aws configure` before working with this demo.
- You will need [Packer](https://www.packer.io/): see the [Packer download page](https://www.packer.io/downloads) for details.
- You will need [Terraform](https://www.terraform.io/): see the [Terraform downlod page](https://www.terraform.io/downloads.html) for details.

## Usage

This demo provides a `Makefile` which can be used to build required artifacts and deploy a small ZendPHP session cluster.
Running `make help` or `make` will provide you with a list of targets and required variables.

1. Create the image.
   To create the image, you will need to provide an email address to use for generating an SSH key to use with the instance.

   ```bash
   $ make cloud-image SSH_KEY_ID={email}
   ```

2. Deploy the cluster.
   After you have created the image, you can use terraform to launch your cluster:

   ```bash
   $ make terraform
   ```

   Terraform will show you the plan it will execute, and ask you to confirm that you wish to perform the actions. Enter "yes" to proceed with the demo.

   When complete, it will detail the public DNS name, as well as public IP, so that you can setup DNS as either a CNAME or A record, respectively.

   At that point you should be able to point a browser to `http://{hostname}` to view the demo.

   Click the "Counter" link repeatedly to demonstrate that the session is indeed persisting across the various web nodes.
   Click "Logout" and revisit the "Counter" page to demonstrate that the session is reset across all web nodes.

3. Teardown.
   When you're done poking around with the demo, clean up by running:

   ```bash
   $ make clean
   ```

## Customizing

To customize the demo to launch your own application, you can do the following:

- Provide your own application in a `.tgz` file as `assets/app.tgz`.
- Modify the deployment script (`scripts/setup.sh`) and instance setup (`scripts/setup-php-redis-config.sh`) with any additional deployment changes.
- Specify a different AWS region via the `make` variable `AWS_REGION`.
- Specify a different instance type via the `make` variable `INSTANCE_TYPE`.
- Specify a different base image via the `images/zendphp.pkr.hcl` file (look for directions in the "zendphp" source section).
