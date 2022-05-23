data "aws_ami" "zendphp" {
  most_recent = true
  owners      = ["self"]

  filter {
    name   = "tag:Extra"
    values = ["zendphp/7.4:ubuntu-20.04"]
  }
}

data "template_file" "redis_php_ini" {
  template = file("./scripts/setup-php-redis-config.sh")

  vars = {
    REDIS_ADDRESS = aws_elasticache_replication_group.redis.primary_endpoint_address
  }
}
