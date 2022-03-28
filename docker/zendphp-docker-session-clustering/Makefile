#!make
########################## Variables ###########################
SHELL   := /bin/bash
HERE    := $(dir $(realpath $(firstword $(MAKEFILE_LIST))))
SWARM   := $(shell docker info | grep Swarm | sed 's/Swarm: //g')
KUBECTL ?= kubectl
K8S_NS  := $(shell $(KUBECTL) get namespace)
################################################################

##### Makefile related #####
.PHONY: 

default: help

##@ Help

help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[0-9a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-40s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Cleanup Tasks
remove-volumes-docker: ## Remove named volumes used to persist dbs
	@printf "\n\033[92mRemoving named volumes to reset data...\033[0m\n\n"
	docker volume rm session-clustering_db-data
	docker volume rm session-clustering_redis-data
	@printf "\n\033[92mVolumes removed.\033[0m\n"

leave-swarm: ## Leave the Docker Swarm 
	docker swarm leave --force

delete-k8s-namespace: ## Delete the project's K8S namespace
	$(KUBECTL) delete namespaces esc

cleanup: ## Cleanup unused Docker resources
	yes | docker system prune

##@ Info Tasks
info-docker: ## Show system info for docker related runs
	@printf "\n\033[92mSystem Info\033[0m\n\n"
	@docker ps -a

info-kube: ## Show system info for K8S run
	@printf "\n\033[92mSystem Info\033[0m\n\n"
	@$(KUBECTL) get pod --namespace esc
	@printf "\n\033[92m-----------------------------------------------------------------------------------------------\033[0m\n\n"
	@$(KUBECTL) get service --namespace esc

##@ Docker Compose Tasks
compose: ## Run Docker Compose
	@printf "\n\033[92mRunning Docker Compose exemplifying the session clustering with Zend PHP and PhpMyAdmin...\033[0m\n\n"
	docker-compose -p session-clustering up -d --build --remove-orphans
	@docker ps -a
	@printf "\n\033[92mServices running!\033[0m\n"

clean-compose: ## Destroy the Docker Compose run
	@printf "\n\033[92mDestroying the 'docker-compose' infrastructure and services...\033[0m\n\n"
	docker-compose -p session-clustering kill
	docker-compose -p session-clustering rm -fsv
	docker-compose -p session-clustering down --remove-orphans
	@printf "\n\033[92mEnvironment destroyed.\033[0m\n"

##@ Docker Swarm Tasks
stack: ## Run Docker Stack on Swarm
	@printf "\n\033[92mDeploying Docker Stack exemplifying the session clustering with Zend PHP and PhpMyAdmin...\033[0m\n\n"
	[[ "inactive" == ${SWARM} ]] && docker swarm init --advertise-addr 192.168.1.151 || exit 0
	docker stack deploy -c docker-stack.yml --prune example-session-clustering
	@printf "\n\033[92mStack deployed.\033[0m\n"

clean-stack: ## Destroy the Docker Stack run
	@printf "\n\033[92mClearing Docker Stack deployment...\033[0m\n\n"
	docker stack rm example-session-clustering 
	@printf "\n\033[92mStack cleared.\033[0m\n"

##@ Kubernetes Tasks
kube: ## Run on K8S
	@printf "\n\033[92mDeploying K8S artifacts exemplifying the session clustering with Zend PHP and PhpMyAdmin...\033[0m\n\n"
	@echo ${K8S_NS} | grep -q "esc" && exit 0 || $(KUBECTL) create namespace esc
	$(KUBECTL) apply -f k8s/ --namespace="esc"
	@printf "\n\033[92mExample has been deployed on K8S.\033[0m\n"

clean-kube: ## Destroy the K8S run
	@printf "\n\033[92mClearing K8S deployment...\033[0m\n\n"
	$(KUBECTL) delete -f k8s/ --namespace="esc"
	@printf "\n\033[92mK8S deployment cleared.\033[0m\n"
