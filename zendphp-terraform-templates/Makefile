#!make
########################## Variables #####################
HERE := $(dir $(realpath $(firstword $(MAKEFILE_LIST))))
##########################################################

SSH_KEY_ID=
AWS_REGION=us-east-2
INSTANCE_TYPE=t2.micro

.PHONY:

date := $(shell date +%Y-%m-%d)

default: help

##@ Help

help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\n\033[1mUsage:\033[0m\n  make \033[36m<target>\033[0m\n"} /^[0-9a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-40s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)
	@printf "\n\033[1mVariables\033[0m\n"
	@printf "  \033[36m%-40s\033[0m \033[1m(REQ for ssh-key and cloud-image)\033[0m %s\n" "SSH_KEY_ID" "User identifier (email) for SSH key"
	@printf "  \033[36m%-40s\033[0m %s\n" "AWS_REGION" "AWS region to deploy to (default: $(AWS_REGION))"
	@printf "  \033[36m%-40s\033[0m %s\n" "INSTANCE_TYPE" "EC2 instance type to create (default: $(INSTANCE_TYPE))"

guard-%:
	@ if [ "${${*}}" = "" ]; then \
		echo "Required variable $* not set"; \
		echo "Run 'make help' or 'make' to see a list of required variables"; \
		exit 1; \
	fi

terraform-ssh-key: guard-SSH_KEY_ID  ## Create SSH credentials
	@printf "\n\033[92mCreating SSH credentials...\033[0m\n"
	ssh-keygen -t rsa -C "$(SSH_KEY_ID)" -f ./terraform-ssh-key
	mv ./terraform-ssh-key.pub ./assets/
	@printf "\n\033[92mSSH credentials created at terraform-ssh-key and assets/terraform-ssh-key.pub.\033[0m\n"

assets/terraform-ssh-key.pub: terraform-ssh-key  ## Create public SSH key

cloud-image: assets/terraform-ssh-key.pub  ## Create cloud image
	@printf "\n\033[92mCreating cloud image with application...\033[0m\n"
	@printf "\n\033[92m...initializing Packer...\033[0m\n"
	packer init images
	@printf "\n\033[92m...building image with Packer...\033[0m\n"
	packer build --var "region=$(AWS_REGION)" --var "instance_type=$(INSTANCE_TYPE)" images
	@printf "\n\033[92mDone creating cloud image with application.\033[0m\n"

terraform:  ## Deploy via terraform
	@printf "\n\033[92mDeploying application via terraform...\033[0m\n"
	terraform init
	terraform apply -var 'app_instance_type=$(INSTANCE_TYPE)' -var 'aws_region=$(AWS_REGION)'
	@printf "\n\033[92mTerraform deployment complete.\033[0m\n"
	@printf "\n\033[92mUpdate DNS and/or /etc/hosts to point at the public_dns_name or public_ip.\033[0m\n"
	terraform output

clean:  ## Tear down Terraform deployment
	@printf "\n\033[92mTearing down application deployment...\033[0m\n"
	terraform destroy -var 'app_instance_type=$(INSTANCE_TYPE)' -var 'aws_region=$(AWS_REGION)'
	@printf "\n\033[92mApplication terminated.\033[0m\n"
