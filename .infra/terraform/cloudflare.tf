provider "cloudflare" {
}

resource "cloudflare_dns_record" "dns" {
  zone_id = "7f33c84c489a7c5ba0a52023eb62aeac"
  name    = "rte-production-monitor.giarel.li"
  type    = "AAAA"
  content = scaleway_instance_ip.v6.address
  ttl     = 1
  proxied = true
}
