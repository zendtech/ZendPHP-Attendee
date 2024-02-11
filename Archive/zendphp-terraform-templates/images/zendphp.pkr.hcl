packer {
  required_plugins {
    amazon = {
      version = ">=1.0.0"
      source  = "github.com/hashicorp/amazon"
    }
  }
}

variable "region" {
  description = "AWS region in which to build and deploy"
  type        = string
  default     = "us-east-2"
}

variable "instance_type" {
  description = "EC2 instance type to use (defaults to t2.micro)"
  type        = string
  default     = "t2.micro"
}

locals {
  timestamp = regex_replace(timestamp(), "[- TZ:]", "")
}

source "amazon-ebs" "zendphp" {
  ami_name      = "session-cluster-demo-${local.timestamp}"
  instance_type = var.instance_type
  region        = var.region
  source_ami_filter {
    filters = {
      # Find a ZendPHP image, using *ZendPHP*{DISTROY} # {VERSION}*{Apache|Nginx}*(BYOL*)?
      # Examples:
      #   - *ZendPHP*Debian 10*Apache*
      #   - *ZendPHP*Centos 8*Nginx*
      #   - *ZendPHP*Ubuntu 20.04*Apache*BYOL
      description         = "*ZendPHP*Ubuntu 20.04*Nginx*BYOL*"
      root-device-type    = "ebs"
      virtualization-type = "hvm"
    }
    most_recent = true
    owners      = ["679593333241"]
  }
  ssh_username = "ubuntu"
  tags         = {
    Extra = "zendphp/7.4:ubuntu-20.04"
  }
}

build {
  sources = ["source.amazon-ebs.zendphp"]

  provisioner "file" {
    source      = "./assets/"
    destination = "/tmp"
  }

  provisioner "shell" {
    script = "./scripts/setup.sh"
  }
}

