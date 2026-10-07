import os
import sys

_project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
_vendor_devops_src = os.path.join(_project_root, 'vendor', 'steevanb', 'python-devops', 'src')
sys.path.insert(0, _vendor_devops_src)

from devops.devops import DevOps
from devops.dockerise import DockerRunDockerise

def create_dev_ops() -> DevOps:
    return DevOps(_project_root)

def create_dockerise(environment: str) -> DockerRunDockerise:
    dev_ops = create_dev_ops()
    return DockerRunDockerise(dev_ops, dev_ops.load_docker_config(environment))
