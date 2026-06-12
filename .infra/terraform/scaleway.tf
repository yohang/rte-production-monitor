resource "scaleway_account_project" "rte_production_monitor" {
  name = "rte-production-monitor"
}

provider "scaleway" {
  zone   = "pl-waw-2"
  region = "pl-waw"
}

resource "scaleway_instance_ip" "v6" {
  type       = "routed_ipv6"
  project_id = scaleway_account_project.rte_production_monitor.id
}

resource "scaleway_instance_security_group" "main" {
  project_id              = scaleway_account_project.rte_production_monitor.id
  name                    = "rte-production-monitor"
  inbound_default_policy  = "accept"
  outbound_default_policy = "accept"
}

resource "scaleway_instance_server" "front" {
  name              = "rte-production-monitor"
  project_id        = scaleway_account_project.rte_production_monitor.id
  security_group_id = scaleway_instance_security_group.main.id
  type              = "STARDUST1-S"
  image             = "debian_trixie"

  enable_dynamic_ip = false
  ip_ids            = [scaleway_instance_ip.v6.id]

  root_volume {
    size_in_gb = 10
  }

  user_data = {
    cloud-init = templatefile("${path.module}/cloud-init.yaml.tpl", {
      tailscale_key     = tailscale_tailnet_key.key.key
      deploy_public_key = tls_private_key.deploy.public_key_openssh
    })
  }
}
