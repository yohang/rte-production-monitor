terraform {
  required_version = ">= 1.6"

  required_providers {
    scaleway = {
      source  = "scaleway/scaleway"
      version = "~> 2.76"
    }
    tailscale = {
      source  = "tailscale/tailscale"
      version = "~> 0.29.2"
    }
    neon = {
      source  = "kislerdm/neon"
      version = "~> 0.13.0"
    }
    github = {
      source  = "integrations/github"
      version = "~> 6.12"
    }
    cloudflare = {
      source  = "cloudflare/cloudflare"
      version = "~> 5.19"
    }
    random = {
      source  = "hashicorp/random"
      version = "~> 3.6"
    }
    tls = {
      source  = "hashicorp/tls"
      version = "~> 4.0"
    }
  }

  backend "s3" {
    bucket  = "yohang-terraform-state"
    key     = "rte-production-monitor/terraform.tfstate"
    profile = "scaleway"
    region  = "fr-par"
    endpoints = {
      s3 = "https://s3.fr-par.scw.cloud"
    }
    skip_credentials_validation = true
    skip_region_validation      = true
    skip_requesting_account_id  = true
  }
}
