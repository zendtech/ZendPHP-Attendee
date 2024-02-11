variable "app_instance_name" {
  description = "Name of application instance"
  type        = string
  default     = "WebServerInstance"
}

variable "app_instance_type" {
  description = "EC2 instance type for app instances"
  type        = string
  default     = "t2.micro"
}

variable "app_instances_max" {
  description = "Maximum number of EC2 instances to deploy"
  type        = number
  default     = 12
}

variable "app_instances_min" {
  description = "Minimum number of EC2 instances to deploy"
  type        = number
  default     = 3
}

variable "app_purpose" {
  description = "Purpose of app instance"
  type        = string
  default     = "session-cluster-demo"
}

variable "aws_region" {
  description = "AWS region in which to launch"
  type        = string
  default     = "us-east-2"
}

variable "health_check_path" {
  description = "Path on web server providing health check URL"
  type        = string
  default     = "/api/ping"
}

variable "redis_node_type" {
  description = "Redis node type (e.g., cache.t3.micro, cache.m5.large) to use"
  type        = string
  default     = "cache.t3.micro"
}

variable "vpc_cidr_block" {
  description = "CIDR block for VPC"
  type        = string
  default     = "10.0.0.0/16"
}

# The private and public CIDR blocks need the SAME NUMBER of entries to ensure
# that the load balancer can reach all private instances.
variable "vpc_private_subnet_cidr_blocks" {
  description = "Available cidr blocks for private subnets"
  type        = list(string)
  default = [
    "10.0.101.0/24",
    "10.0.102.0/24",
    "10.0.103.0/24",
  ]
}

# The private and public CIDR blocks need the SAME NUMBER of entries to ensure
# that the load balancer can reach all private instances.
variable "vpc_public_subnet_cidr_blocks" {
  description = "Available cidr blocks for public subnets"
  type        = list(string)
  default = [
    "10.0.1.0/24",
    "10.0.2.0/24",
    "10.0.3.0/24",
  ]
}

variable "vpc_redis_subnet_cidr_blocks" {
  description = "Available cidr blocks for Redis subnet"
  type        = list(string)
  default = [
    "10.0.201.0/24",
    "10.0.202.0/24",
    "10.0.203.0/24",
  ]
}
