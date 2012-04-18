#!/usr/bin/env python

from BaseHTTPServer import BaseHTTPRequestHandler, HTTPServer
from SocketServer import ForkingMixIn
import traceback
import urllib
import select
import fcntl
import time
import sys
import os

bsize = 32768
MAXFD = 1024
REDIRECT_TO = "/dev/null"

def createDaemon():
   """Detach a process from the controlling terminal and run it in the
   background as a daemon.
   """

   try:
      pid = os.fork()
   except OSError, e:
      raise Exception, "%s [%d]" % (e.strerror, e.errno)

   if (pid == 0):    # The first child.
      os.setsid()
      try:
         pid = os.fork()    # Fork a second child.
      except OSError, e:
         raise Exception, "%s [%d]" % (e.strerror, e.errno)

      if (pid == 0):    # The second child.
         pass
      else:
         os._exit(0)    # Exit parent (the first child) of the second child.
   else:
      os._exit(0)    # Exit parent of the first child.

   import resource        # Resource usage information.
   maxfd = resource.getrlimit(resource.RLIMIT_NOFILE)[1]
   if (maxfd == resource.RLIM_INFINITY):
      maxfd = MAXFD
 
   for fd in range(0, maxfd):
      try:
         os.close(fd)
      except OSError:    # ERROR, fd wasn't open to begin with (ignored)
         pass

   os.open(REDIRECT_TO, os.O_RDWR)    # standard input (0)
   os.dup2(0, 1)            # standard output (1)
   os.dup2(0, 2)            # standard error (2)
   return(0)

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        url_path = self.path.split("/")
        url_path = [p for p in url_path if len(p)>1]
        self.send_response(200)
        self.send_header("Content-type","text/html")
        self.end_headers()
        (junk,fp) = os.popen4(command,"t")
        fl = fcntl.fcntl(fp,fcntl.F_GETFL)
        fcntl.fcntl(fp,fcntl.F_SETFL,fl | os.O_NONBLOCK)
        while True:
            (r,w,e) = select.select([fp],[],[],0.2)
            if not fp in r:
                self.wfile.write(".")
                continue
            else:
                data = fp.read(bsize)
                if len(data) == 0:
                    break
                self.wfile.write(data)
        return
try:
    createDaemon()
    command = sys.argv[1]
    server = HTTPServer(('',8080),Handler)
    server.handle_request()
except:
    fp = open("/tmp/pyserver.log","a")
    fp.write("Starting log\n")
    fp.flush()    
    fp.write("Error: %s\n"%traceback.format_exc())
    fp.flush()
