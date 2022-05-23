output "public_dns_name" {
  description = "Public DNS name of load balancer"
  value       = aws_lb.web.dns_name
}
