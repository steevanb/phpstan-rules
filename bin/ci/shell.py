#!/usr/bin/env python3
import os
import sys

sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..')))

from bin.bootstrap import create_dockerise

create_dockerise("ci").exec_stream(sys.argv[1:])
