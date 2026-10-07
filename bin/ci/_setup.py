import os
import sys

sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..')))

from bin.bootstrap import create_dockerise
from devops.ci.runner import CiRunner

ci = CiRunner(create_dockerise("ci"))
