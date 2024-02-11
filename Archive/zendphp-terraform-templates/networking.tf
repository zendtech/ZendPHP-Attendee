# Create a list of availability zones
data "aws_availability_zones" "all" {
}

module "vpc" {
  source  = "terraform-aws-modules/vpc/aws"
  version = "3.2.0"

  cidr = var.vpc_cidr_block

  azs                 = data.aws_availability_zones.all.names
  elasticache_subnets = var.vpc_redis_subnet_cidr_blocks
  private_subnets     = var.vpc_private_subnet_cidr_blocks
  public_subnets      = var.vpc_public_subnet_cidr_blocks

  create_elasticache_subnet_group = false
  enable_nat_gateway              = true
  enable_vpn_gateway              = false
}

resource "aws_elasticache_subnet_group" "redis" {
  name       = "redis-subnet-${var.app_purpose}"
  subnet_ids = module.vpc.elasticache_subnets
}

# Create a security group for the ELB to allow web accessibility
resource "aws_security_group" "elb" {
  name        = "load-balancer-sg-${var.app_purpose}"
  description = "Load balancer security group"
  vpc_id      = module.vpc.vpc_id

  # HTTP access from anywhere
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  # Outbound internet access
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# Create a security group for the instances to allow HTTP from ELB
resource "aws_security_group" "webserver" {
  name        = "webserver-sg-${var.app_purpose}"
  description = "Webserver security group"
  vpc_id      = module.vpc.vpc_id

  # HTTP access from elb
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = module.vpc.public_subnets_cidr_blocks
  }

  # Outbound internet access
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# Create a security group for the Redis cluster
resource "aws_security_group" "redis" {
  name        = "redis-sg-${var.app_purpose}"
  description = "Redis security group"
  vpc_id      = module.vpc.vpc_id

  # Access from HTTP servers
  ingress {
    from_port   = 6379
    to_port     = 6379
    protocol    = "tcp"
    cidr_blocks = module.vpc.private_subnets_cidr_blocks
  }

  # Outbound internet access
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}
