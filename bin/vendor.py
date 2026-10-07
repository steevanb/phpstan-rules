import os
import subprocess
import sys

_project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
_auth_file = os.path.expanduser('~/.config/composer/auth.json')

def composer_command(environment: str, arguments: list[str]) -> list[str]:
    config_path = os.path.join(_project_root, 'config', 'devops', f'docker.{environment}.py')
    config_globals: dict = {}
    with open(config_path) as f:
        exec(f.read(), config_globals)
    image: str = config_globals['DockerConfig'].IMAGE

    if not os.path.isfile(_auth_file):
        print(f"Missing {_auth_file} — create it with a GitHub token to install private deps.", file=sys.stderr)
        sys.exit(1)

    # composer.lock is not committed, so every install resolves steevanb/python-devops from its private
    # VCS repository: the GitHub token has to be mounted, which DockerRunDockerise does not do.
    return [
        "docker", "run", "--rm",
        "-v", f"{_project_root}:/app", "-w", "/app",
        "--user", f"{os.getuid()}:{os.getgid()}",
        "-e", "COMPOSER_HOME=/composer",
        "-v", f"{_auth_file}:/composer/auth.json:ro",
        image,
        "composer", "--no-interaction", "--ansi",
    ] + arguments

def ensure_vendor(environment: str) -> None:
    vendor_dir = os.path.join(_project_root, 'vendor', 'steevanb', 'python-devops')
    if os.path.isdir(vendor_dir):
        return

    print("vendor/steevanb/python-devops missing — running composer install...")
    subprocess.run(composer_command(environment, ["install"]), check=True)
