# Create the load balancer
resource "aws_lb" "web" {
  name               = "lb-${var.app_purpose}"
  internal           = false
  load_balancer_type = "application"
  security_groups    = [aws_security_group.elb.id]
  subnets            = module.vpc.public_subnets
}

# Create the target group for the load balancer and define health checks
resource "aws_lb_target_group" "http" {
  name     = "lb-tg-${var.app_purpose}"
  port     = 80
  protocol = "HTTP"
  vpc_id   = module.vpc.vpc_id

  health_check {
    healthy_threshold   = 2
    interval            = 30
    matcher             = "200"
    path                = var.health_check_path
    port                = 80
    protocol            = "HTTP"
    timeout             = 3
    unhealthy_threshold = 2
  }
}

# Define what the load balancer will listen for, and what it will do
resource "aws_lb_listener" "http" {
  load_balancer_arn = aws_lb.web.arn
  port              = "80"
  protocol          = "HTTP"

  default_action {
    type = "forward"
    target_group_arn = aws_lb_target_group.http.arn
  }
}

# Define what instances will look like in the autoscaling group
resource "aws_launch_configuration" "app" {
  image_id        = data.aws_ami.zendphp.id
  instance_type   = var.app_instance_type
  security_groups = [aws_security_group.webserver.id]
  user_data       = data.template_file.redis_php_ini.rendered

  lifecycle {
    create_before_destroy = true
  }
}

# Define the autoscaling group
resource "aws_autoscaling_group" "app" {
  name                 = "${var.app_purpose}-${aws_launch_configuration.app.name}"
  launch_configuration = aws_launch_configuration.app.id
  vpc_zone_identifier  = module.vpc.private_subnets
  target_group_arns    = [aws_lb_target_group.http.arn]
  health_check_type    = "ELB"

  min_size         = var.app_instances_min
  max_size         = var.app_instances_max
  min_elb_capacity = var.app_instances_min

  lifecycle {
    create_before_destroy = true
  }

  tag {
    key = "Name"
    value = "asg-${var.app_purpose}"
    propagate_at_launch = true
  }
}

# Define the Redis cluster
resource "aws_elasticache_replication_group" "redis" {
  replication_group_id          = "redis-${var.app_purpose}"
  replication_group_description = "Redis session cluster"
  engine                        = "redis"
  node_type                     = var.redis_node_type
  port                          = 6379
  automatic_failover_enabled    = false

  security_group_ids = [aws_security_group.redis.id]
  subnet_group_name  = aws_elasticache_subnet_group.redis.name
  
  multi_az_enabled      = false
  availability_zones    = data.aws_availability_zones.all.names
  number_cache_clusters = length(data.aws_availability_zones.all.names)
}
