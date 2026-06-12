provider "github" {
  owner = "yohang"
}

resource "random_bytes" "app_secret" {
  length = 32
}

resource "tls_private_key" "deploy" {
  algorithm = "ED25519"
}

resource "github_repository_environment" "deploy" {
  repository  = "rte-production-monitor"
  environment = "prod"
}

resource "github_actions_environment_secret" "ssh_key" {
  repository  = "rte-production-monitor"
  environment = github_repository_environment.deploy.environment
  secret_name = "SSH_PRIVATE_KEY"
  value       = tls_private_key.deploy.private_key_openssh
}

resource "github_actions_environment_secret" "ssh_host" {
  repository  = "rte-production-monitor"
  environment = github_repository_environment.deploy.environment
  secret_name = "SSH_HOST"
  value       = scaleway_instance_ip.v6.address
}

resource "github_actions_environment_secret" "ssh_user" {
  repository  = "rte-production-monitor"
  environment = github_repository_environment.deploy.environment
  secret_name = "SSH_USER"
  value       = "debian"
}

resource "github_actions_environment_secret" "database_url" {
  repository  = "rte-production-monitor"
  environment = github_repository_environment.deploy.environment
  secret_name = "DATABASE_URL"
  value       = neon_project.rte-production-monitor.connection_uri
}

resource "github_actions_environment_secret" "app_secret" {
  repository  = "rte-production-monitor"
  environment = github_repository_environment.deploy.environment
  secret_name = "APP_SECRET"
  value       = random_bytes.app_secret.hex
}

resource "github_actions_environment_variable" "app_env" {
  repository    = "rte-production-monitor"
  environment   = github_repository_environment.deploy.environment
  variable_name = "APP_ENV"
  value         = "prod"
}

resource "github_actions_environment_variable" "server_name" {
  repository    = "rte-production-monitor"
  environment   = github_repository_environment.deploy.environment
  variable_name = "SERVER_NAME"
  value         = "rte-production-monitor.giarel.li"
}
