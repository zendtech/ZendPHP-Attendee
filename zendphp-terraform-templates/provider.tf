provider "aws" {
  profile = "default"
  region  = "${var.aws_region}"

  default_tags {
    tags = {
      Application = var.app_purpose
    }
  }
}
