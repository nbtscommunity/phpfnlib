# Copyright (c) 2000 LOGILAB S.A. (Paris, FRANCE).
# http://www.logilab.fr/ -- mailto:contact@logilab.fr
#
# This program is free software; you can redistribute it and/or modify it under
# the terms of the GNU General Public License as published by the Free Software
# Foundation; either version 2 of the License, or (at your option) any later
# version.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along with
# this program; if not, write to the Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
"""
this module provides classes to format the native tree2tree output
"""
__revision__ = '$Id: format.py,v 1.23 2002/09/16 08:50:57 syt Exp $'

import types
try:
    from xml.dom import EMPTY_NAMESPACE as NO_NS
except:
    NO_NS = None
from objects import A_N1, A_N2, A_DESC, caract, xml_print, f_xpath,\
     XUPD_PREFIX, XUPD_URI, to_dom

def get_attrs_string(attrs):
    """ extract and return a string corresponding to an attributes list """
    attr_s = ''
    for attr_n, attr_v in attrs:
        attr_s = '%s %s="%s" '%(attr_s, attr_n, attr_v)
    return attr_s

## XUPDATE FUNCTIONS ###########################################################

def open_xupdate_node(type, attrs, indent=''):
    print '%s<%s:%s%s>'%(indent, XUPD_PREFIX, type, get_attrs_string(attrs))

def close_xupdate_node(action, indent=''):
    print '%s</%s:%s>'%(indent, XUPD_PREFIX, action)

def write_xupdate_node(type, attrs, indent=''):
    print '%s<%s:%s%s/>'%(indent, XUPD_PREFIX, type, get_attrs_string(attrs))
    

## Formatter interface #########################################################
class AbstractFormatter:

    def init(self):
        """ method called before the begining of the tree 2 tree correction """
        self.edit_s = []
    
    def add_action(self, action):
        """ method called when an action is added to the edit script """
        self.edit_s.append(action)

    def format_action(self, action):
        """ method called by end() to format each action in the edit script
        at least this method should be overriden
        """
        raise NotImplementedError()

    def end(self):
        """ method called at the end of the tree 2 tree correction """
        for action in self.edit_s:
            self.format_action(action)


## Internal Formatter ##########################################################

class InternalPrinter(AbstractFormatter):
    """ print actions in the internal format """
    
    def add_action(self, action):
        if len(action) > 2 and type(action[A_N2]) == types.ListType:
            if type(action[A_N1]) == types.ListType:
                #swap or move node
                action[A_N1] = f_xpath(action[A_N1])
                action[A_N2] = f_xpath(action[A_N2])
        AbstractFormatter.add_action(self, action)
        
    def format_action(self, action):
        if len(action) > 2 and type(action[A_N2]) == types.ListType:
            print '['+action[A_DESC]+', '+action[A_N1]+','
            xml_print(action[A_N2])
            print "]"
        elif len(action) > 2:
            print '['+action[A_DESC]+', '+action[A_N1]+', '+action[A_N2]+']'
        else:
            print '['+action[A_DESC]+', '+action[A_N1]+']'


## XUpdate Formatters (text / DOM) #############################################

class XUpdateMixIn:
    """ XUpdate mixin to preprocess added actions """

    def add_action(self, action):
        if action[A_DESC] == 'move-first':
            # replace move-first with remove and insert (sibling nodes)
            self.edit_s.append(['remove', f_xpath(action[A_N1])])
            self.edit_s.append(['append', f_xpath(action[A_N2]), action[A_N1]])
        elif action[A_DESC] == 'move-after':
            # replace move-after with remove and insert (sibling nodes)
            self.edit_s.append(['remove', f_xpath(action[A_N1])])
            self.edit_s.append(['insert-after', f_xpath(action[A_N2]), action[A_N1]])
        elif action[A_DESC] == 'move-and-rename':
            # replace move-and-rename with remove and insert (sibling nodes)
            self.edit_s.append(['remove', f_xpath(action[A_N1])])
            self.edit_s.append(['insert-after', f_xpath(action[A_N2][N_PARENT]), action[A_N2]])
        elif action[A_DESC] == 'swap':
            # replace swap with remove and insert (sibling nodes)
            self.edit_s.append(['remove', f_xpath(action[A_N2])])
            self.edit_s.append(['insert-after', f_xpath(action[A_N1]), action[A_N2]])
        else:
            self.edit_s.append(action)

        
class XUpdatePrinter(XUpdateMixIn, AbstractFormatter):
    """ take the actions list in standard format and output it following
    Xupdate xml specification
    """
    def init(self):
        AbstractFormatter.init(self)
        print '''<?xml version="1.0"?> 
<xupdate:modifications version="1.0"
 xmlns:%s="%s">
''' % (XUPD_PREFIX, XUPD_URI)
        
    def format_action(self, action):
        if action[A_DESC] == 'remove': 
            write_xupdate_node(action[A_DESC], [['select', action[A_N1]]], '  ')
        elif action[A_DESC] == 'append-last':
            open_xupdate_node('append', [['select', action[A_N1]], ['child', 'last()']], '  ')
            xml_print(action[A_N2], '    ', xupdate=1)
            close_xupdate_node('append', '  ')
        elif action[A_DESC] == 'append-first':
            open_xupdate_node('append', [['select', action[A_N1]], ['child', 'first()']], '  ')
            xml_print(action[A_N2], '    ', xupdate=1)
            close_xupdate_node('append', '  ')
        elif action[A_DESC] in ['append', 'insert-after']:
            open_xupdate_node(action[A_DESC], [['select', action[A_N1]]], '  ')
            xml_print(action[A_N2], '    ', xupdate=1)
            close_xupdate_node(action[A_DESC], '  ')
        elif action[A_DESC] == 'rename': 
            open_xupdate_node(action[A_DESC], [['name', action[A_N1]]], '  ')
            print action[A_N2]
            close_xupdate_node(action[A_DESC], '  ')
        else:
            open_xupdate_node(action[A_DESC], [['select', action[A_N1]]], '  ')
            print action[A_N2]
            close_xupdate_node(action[A_DESC], '  ')
        print

    def end(self):
        AbstractFormatter.end(self)
        print '</%s:modifications>'%XUPD_PREFIX


class DOMXUpdateFormatter(XUpdateMixIn, AbstractFormatter):
    """ take the actions list in standard format and return a dom tree
    which follow Xupdate xml specification (without xupdate namespace)
    dom tree is append to doc (DOM Document node)
    """
    def __init__(self, doc, encoding='UTF-8'):
        self.doc = doc
        self.encoding = encoding
        
    def init(self):
        AbstractFormatter.init(self)
        output = self.doc.createElementNS(XUPD_URI, '%s:modifications'%XUPD_PREFIX)
        output.setAttributeNS(NO_NS, 'version', '1.0')
        self.output = output
        
    def format_action(self, action):
        doc = self.doc
        if action[A_DESC] == 'remove':
            node = doc.createElementNS(XUPD_URI, '%s:%s' % (XUPD_PREFIX, action[A_DESC]))
            node.setAttributeNS(NO_NS, 'select', action[A_N1])
        elif action[A_DESC] == 'append-first':
            node = doc.createElementNS(XUPD_URI, '%s:%s'% (XUPD_PREFIX, 'append'))
            node.setAttributeNS(NO_NS, 'select', action[A_N1])
            node.setAttributeNS(NO_NS, 'child', 'first()')
            node.appendChild(to_dom(action[A_N2], doc, XUPD_URI, XUPD_PREFIX))
        elif action[A_DESC] == 'append-last':
            node = doc.createElementNS(XUPD_URI, '%s:%s' % (XUPD_PREFIX, 'append'))
            node.setAttributeNS(NO_NS, 'select', action[A_N1])
            node.setAttributeNS(NO_NS, 'child', 'last()')
            node.appendChild(to_dom(action[A_N2], doc, XUPD_URI, XUPD_PREFIX))
        elif action[A_DESC] in ['append', 'insert-after', 'insert-before']:
            node = doc.createElementNS(XUPD_URI, '%s:%s' % (XUPD_PREFIX, action[A_DESC]))
            node.setAttributeNS(NO_NS, 'select', action[A_N1])
            node.appendChild(to_dom(action[A_N2], doc, XUPD_URI, XUPD_PREFIX))
        elif action[A_DESC] == 'rename': 
            node = doc.createElementNS(XUPD_URI, '%s:%s' %(XUPD_PREFIX, action[A_DESC]))
            node.setAttributeNS(NO_NS, 'name', action[A_N1])
            #print action[A_N2], type(action[A_N2])
            v = unicode(action[A_N2], self.encoding)
            node.appendChild(doc.createTextNode(v))
        else:
            node = doc.createElementNS(XUPD_URI, '%s:%s' % (XUPD_PREFIX, action[A_DESC]))
            node.setAttributeNS(NO_NS, 'select', action[A_N1])
            v = unicode(action[A_N2], self.encoding)
            node.appendChild(doc.createTextNode(v))
        # append xupdate node
        self.output.appendChild(node)

