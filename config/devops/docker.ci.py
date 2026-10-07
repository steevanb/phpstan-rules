class DockerConfig:
    IMAGE: str = "ghcr.io/steevanb/phpstan-rules:ci"
    CONTAINER_NAME: str = "phpstan-rules_ci"
    DOCKERFILE_DIRECTORY: str = "docker/ci"
