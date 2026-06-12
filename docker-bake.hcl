variable "IMAGE_PREFIX" {
    default = "ghcr.io/yohang/rte-production-monitor/"
}

variable "TAGS" {
    default = "latest"
}

group "default" {
    targets = ["app"]
}

target "app" {
    tags = [for t in split(",", TAGS) : "${IMAGE_PREFIX}app:${t}"]
    cache-from = ["type=registry,ref=${IMAGE_PREFIX}app:cache"]
    cache-to   = ["type=registry,ref=${IMAGE_PREFIX}app:cache,mode=max"]
}
