#!/usr/bin/env python

"""Xmldiff Setup script"""

from distutils.core import setup, Extension
from distutils import util

s_desc = "Tree 2 tree correction between xml documents"
l_desc = "Xmldiff is a utility for extracting differences between two xml files. It return a set of primitives to apply on source tree to obtain the destination tree."


def EnsureScripts(*linuxScripts):
    """
    Creates the proper script names required for each platform
    (taken from 4Suite)
    """
    scripts = linuxScripts
    if util.get_platform()[:3] == 'win':
        scripts = map(lambda s: s + '.bat', scripts)
    return scripts

from logilab.xmldiff import modname, version

if __name__ == '__main__' :
    setup(name = modname,
          version = version,
          licence ="GPL",
          description = s_desc,
          author = "Logilab",
          author_email = "devel@logilab.fr",
          url = "http://www.logilab.org/xmldiff",
          packages = ['logilab','logilab.xmldiff'],
          scripts = EnsureScripts('bin/xmldiff'),
          long_description = l_desc,
          # c extensions
          ext_modules = [Extension('logilab.xmldiff.maplookup', ['logilab/xmldiff/extensions/maplookup.c'])]
          )

      
