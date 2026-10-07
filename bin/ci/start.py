#!/usr/bin/env python3

import os
import sys

_project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))

sys.path.insert(0, _project_root)

from bin.vendor import composer_command, ensure_vendor

ensure_vendor("ci")

import _setup
from bin.bootstrap import create_dev_ops
from devops.step_runner import StepRunner

dev_ops = create_dev_ops()
local: bool = "--local" in sys.argv[1:] or "-l" in sys.argv[1:]

steps: list[tuple[str, list[str]]] = []
if not local:
    steps.append(("Pull CI image", ["docker", "pull", dev_ops.load_docker_config("ci").IMAGE]))
steps.append(("Install dependencies", composer_command("ci", ["update", "--optimize-autoloader"])))

StepRunner(steps, "CI ready.", dev_ops.verbosity).run()
