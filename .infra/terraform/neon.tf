provider "neon" {

}

resource "neon_project" "rte-production-monitor" {
  name                      = "rte-production-monitor"
  pg_version                = 18
  region_id                 = "aws-eu-central-1"
  history_retention_seconds = 21600

  # Configure default branch settings (optional)
  branch {
    name          = "production"
    database_name = "app_db"
    role_name     = "app_admin"
  }

  # Configure default endpoint settings (optional)
  default_endpoint_settings {
    autoscaling_limit_min_cu = 0.25
    autoscaling_limit_max_cu = 0.25
  }
}
