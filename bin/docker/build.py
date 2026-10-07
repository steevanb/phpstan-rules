#!/usr/bin/env python3

import atexit
import json
import os
import shutil
import subprocess
import sys
import tempfile
import _setup
from bin.bootstrap import create_dev_ops
from devops.docker.build import DockerBuildRunner, DockerImageConfig

def read_docker_config(docker_config_path: str) -> dict:
    try:
        with open(os.path.join(docker_config_path, "config.json"), "r", encoding="utf-8") as file:
            return json.load(file)
    except (OSError, ValueError):
        return {}

def is_credential_helper_runnable(helper: str) -> bool:
    executable = shutil.which("docker-credential-" + helper)
    if executable is None:
        return False

    try:
        return subprocess.run(
            [executable, "list"],
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
            timeout=10,
        ).returncode == 0
    except (OSError, subprocess.SubprocessError):
        return False

def find_broken_credential_helpers(config: dict) -> set[str]:
    helpers = {helper for helper in [config.get("credsStore")] if isinstance(helper, str)}
    helpers.update(
        helper
        for helper in (config.get("credHelpers") or {}).values()
        if isinstance(helper, str)
    )

    return {helper for helper in helpers if is_credential_helper_runnable(helper) is False}

def remove_credential_helpers(config: dict, helpers: set[str]) -> dict:
    sanitized = dict(config)
    if sanitized.get("credsStore") in helpers:
        del sanitized["credsStore"]

    remaining_helpers = {
        registry: helper
        for registry, helper in (sanitized.get("credHelpers") or {}).items()
        if helper not in helpers
    }
    if len(remaining_helpers) > 0:
        sanitized["credHelpers"] = remaining_helpers
    else:
        sanitized.pop("credHelpers", None)

    return sanitized

def create_docker_config_directory(docker_config_path: str, config: dict) -> str:
    sanitized_path = tempfile.mkdtemp(prefix="docker-config-")
    atexit.register(shutil.rmtree, sanitized_path, True)

    if os.path.isdir(docker_config_path):
        for entry in os.listdir(docker_config_path):
            if entry != "config.json":
                os.symlink(os.path.join(docker_config_path, entry), os.path.join(sanitized_path, entry))

    with open(os.path.join(sanitized_path, "config.json"), "w", encoding="utf-8") as file:
        json.dump(config, file)

    return sanitized_path

def has_stored_credentials(config: dict) -> bool:
    return any(
        isinstance(auth, dict) and (auth.get("auth") is not None or auth.get("identitytoken") is not None)
        for auth in (config.get("auths") or {}).values()
    )

def ignore_broken_credential_helpers(push: bool) -> None:
    docker_config_path = os.environ.get("DOCKER_CONFIG", os.path.expanduser("~/.docker"))
    config = read_docker_config(docker_config_path)
    broken_helpers = find_broken_credential_helpers(config)
    if len(broken_helpers) == 0:
        return

    sanitized_config = remove_credential_helpers(config, broken_helpers)
    os.environ["DOCKER_CONFIG"] = create_docker_config_directory(docker_config_path, sanitized_config)

    print(
        f"\033[33mDocker credential helper {', '.join(sorted(broken_helpers))} cannot be executed, "
        f"building without it.\033[0m"
    )
    if push and has_stored_credentials(sanitized_config) is False:
        print(
            "\033[33mPushing needs credentials that helper holds: remove \"credsStore\" from "
            f"{os.path.join(docker_config_path, 'config.json')} and run "
            "\033[32mdocker login ghcr.io\033[33m again.\033[0m"
        )

push = "--push" in sys.argv[1:]
ignore_broken_credential_helpers(push)

dev_ops = create_dev_ops()

ci_config = dev_ops.load_docker_config("ci")

DockerBuildRunner(
    [
        DockerImageConfig("phpstan-rules_ci", ci_config.IMAGE, os.path.join(dev_ops.project_path, ci_config.DOCKERFILE_DIRECTORY)),
    ],
    dev_ops,
    push=push,
).run()
