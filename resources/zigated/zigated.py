#!/usr/bin/env python3
'''
zigated daemon
created by Sebastien RAMAGE
sebastien.ramage@gmail.com
01/01/2018
'''
import logging
import os
import sys
import time
import signal
import json
import argparse
import zigate
import socketserver
import requests
import threading
import uuid
import subprocess

BASE_PATH = os.path.join(os.path.dirname(__file__), '..', '..', '..', '..')
BASE_PATH = os.path.abspath(BASE_PATH)


class JeedomCallback:
    def __init__(self, apikey, url):
        self.apikey = apikey
        self.url = url
        self.messages = []
        self._stop = False
        self.t = threading.Thread(target=self.run)
        self.t.setDaemon(True)
        self.t.start()

    def stop(self):
        self._stop = True

    def run(self):
        while not self._stop:
            while self.messages:
                m = self.messages.pop(0)
                try:
                    self._request(m)
                except Exception as error:
                    logging.error('Error on send request to jeedom {}'.format(error))
            time.sleep(0.5)

    def _request(self, m):
        response = None
        logging.debug('Send to jeedom :  {}'.format(m))
        r = requests.post('{}?apikey={}'.format(self.url, self.apikey),
                          data=json.dumps(m, cls=zigate.core.DeviceEncoder),
                          verify=False)
        if r.status_code != 200:
            logging.error('Error on send request to jeedom, return code {} - {}'.format(r.status_code, r.reason))

        else:
            response = r.json()
            logging.debug('Jeedom reply :  {}'.format(response))
        return response

    def send(self, message):
        self.messages.append(message)

    def send_now(self, message):
        return self._request(message)

    def test(self):
        logging.debug('Send to test to jeedom')
        r = self.send_now({'action': 'test'})
        if not r or not r.get('success'):
            logging.error('Calling jeedom failed')
            return False
        return True


class JeedomHandler(socketserver.BaseRequestHandler):
    def handle(self):
        # self.request is the TCP socket connected to the client
        data = self.request.recv(1024)
        logging.debug("Message received in socket")
        message = json.loads(data.decode())
        lmessage = dict(message)
        del lmessage['apikey']
        logging.debug(lmessage)
        if message.get('apikey') != _apikey:
            logging.error("Invalid apikey from socket : {}".format(data))
            return
        response = {'result': None, 'success': True}
        action = message.get('action')
        args = message.get('args')
        if hasattr(z, action):
            func = getattr(z, action)
            response['result'] = func
            if callable(response['result']):
                response['result'] = response['result'](*args)
        elif hasattr(self, action):
            func = getattr(self, action)
            response['result'] = func
            if callable(response['result']):
                response['result'] = response['result'](*args)
        if isinstance(response['result'], zigate.responses.Response):
            response['result'] = response['result'].data
        logging.debug(response)
        self.request.sendall(json.dumps(response, cls=zigate.core.DeviceEncoder).encode())

    def get_libversion(self):
        return zigate.__version__
    
    def raw_command(self, cmd, data):
        cmd = cmd.lower()
        if 'x' in cmd:
            cmd = int(cmd, 16)
        else:
            cmd = int(cmd)
        return z.send_data(cmd, data)


def handler(signum=None, frame=None):
    logging.debug("Signal %i caught, exiting..." % int(signum))
    shutdown()


def shutdown():
    logging.debug("Shutdown")
    logging.debug("Saving zigate state")
    z.save_state()
    logging.debug("Closing zigate")
    z.close()
    logging.debug("Shutting down callback server")
    jc.stop()
    logging.debug("Shutting down local server")
    server.shutdown()
    logging.debug("Removing PID file " + str(_pidfile))
    if os.path.exists(_pidfile):
        os.remove(_pidfile)
    logging.debug("Removing Socket file " + str(_sockfile))
    if os.path.exists(_sockfile):
        os.remove(_sockfile)
    logging.debug("Exit 0")


def callback_command(sender, signal, **kwargs):
    logging.debug('External command {} : {}'.format(signal, kwargs))
    if 'zigate' in kwargs:
        kwargs.pop('zigate')
    cmd = {'action': signal}
    cmd.update(kwargs)
    jc.send(cmd)


def convert_log_level(level='error'):
    LEVELS = {'debug': logging.DEBUG,
              'info': logging.INFO,
              'notice': logging.WARNING,
              'warning': logging.WARNING,
              'error': logging.ERROR,
              'critical': logging.CRITICAL,
              'none': logging.NOTSET}
    return LEVELS.get(level, logging.NOTSET)


def sharedata():
    '''
    Send anonymous file to help supporting new device
    '''
    key = ''
    if os.path.exists(key_file):
        with open(key_file, 'r') as fp:
            key = fp.read()
            if key == '3d5c898817b211e88b59080027ca08a3':  # banned key
                key = ''
    if not key:
        key = uuid.uuid1().hex
        with open(key_file, 'w') as fp:
            fp.write(key)
    time.sleep(60)
    while True:
        try:
            try_sharedata(key, persistent_file)
            try_sharedata('z'+key, os.path.join(BASE_PATH, 'log', 'zigate'))
            try_sharedata('zu'+key, os.path.join(BASE_PATH, 'log', 'zigate_update'))
            try_sharedata('http.error.'+key, os.path.join(BASE_PATH, 'log', 'http.error'))
        except Exception:
            pass
        time.sleep(60*60*6)


def try_sharedata(key, file):
    '''
    Send anonymous file to help supporting new device
    '''
    if os.path.exists(file):
        payload = {'key': key}
        with open(file, 'rb') as fp:
            files = {'file': fp}
            requests.post('http://doudz.pythonanywhere.com',
                          data=payload,
                          files=files)


def checkPlugins():
    plugins = [('Abeille', 'AbeilleSerialRead.php')]
    for plugin, to_check in plugins:
        p = os.path.join(BASE_PATH, 'plugins', plugin)
        if os.path.exists(p):
            logging.info('Plugin {} détecté, vérification de l\'état'.format(plugin))
            proc = subprocess.check_output('ps -e -f', shell=True).decode()
            if to_check in proc:
                logging.error('Le plugin {} est en cours de fonctionnement, veuillez l\'arrêter'.format(plugin))
                return False
            else:
                logging.info('Ok, le plugin {} ne semble pas en fonctionnement.'.format(plugin))
    return True


parser = argparse.ArgumentParser()
parser.add_argument('--loglevel', help='LOG Level', default='error')
parser.add_argument('--socket', help='Daemon socket',
                    default='/tmp/zigated.sock')
parser.add_argument('--pidfile', help='PID File', default='/tmp/zigated.pid')
parser.add_argument('--apikey', help='API Key', default='nokey')
parser.add_argument('--device', help='ZiGate port', default='auto')
parser.add_argument('--callback', help='Jeedom callback', default='http://localhost')
parser.add_argument('--sharedata', type=int, default=1)
parser.add_argument('--channel', type=int, default=None)
args = parser.parse_args()

FORMAT = '[%(asctime)-15s][%(levelname)s][%(name)s](%(threadName)s) : %(message)s'
logging.basicConfig(level=convert_log_level(args.loglevel),
                    format=FORMAT, datefmt="%Y-%m-%d %H:%M:%S")
urllib3_logger = logging.getLogger('urllib3')
urllib3_logger.setLevel(logging.CRITICAL)

logging.info('Start zigated')
logging.info('Log level : {}'.format(args.loglevel))
logging.info('Socket : {}'.format(args.socket))
logging.info('PID file : {}'.format(args.pidfile))
logging.info('Apikey : {}'.format(args.apikey))
logging.info('Device : {}'.format(args.device))
logging.info('Callback : {}'.format(args.callback))

logging.info('Python version : {}'.format(sys.version))
logging.info('zigate version : {}'.format(zigate.__version__))

if not checkPlugins():
    sys.exit(1)

_pidfile = args.pidfile
_sockfile = args.socket
_apikey = args.apikey

signal.signal(signal.SIGINT, handler)
signal.signal(signal.SIGTERM, handler)


persistent_file = os.path.join(os.path.dirname(__file__), 'zigate.json')
# old version
if not os.path.exists(persistent_file):
    if os.path.exists(os.path.join(os.path.dirname(__file__), '.zigate.json')):
        os.rename(os.path.join(os.path.dirname(__file__), '.zigate.json'),
                  os.path.join(os.path.dirname(__file__), 'zigate.json'))

key_file = os.path.join(os.path.dirname(__file__), '.key')


pid = str(os.getpid())
logging.debug("Writing PID " + pid + " to " + str(args.pidfile))
with open(args.pidfile, 'w') as fp:
    fp.write("%s\n" % pid)

jc = JeedomCallback(args.apikey, args.callback)
if not jc.test():
    sys.exit()

zigate.dispatcher.connect(callback_command, zigate.ZIGATE_FAILED_TO_CONNECT)

if os.path.exists(args.socket):
    os.unlink(args.socket)
server = socketserver.UnixStreamServer(args.socket, JeedomHandler)
if '.' in args.device:  # supposed I.P:PORT
    host_port = args.device.split(':', 1)
    host = host_port[0]
    port = None
    if len(host_port) == 2:
        port = int(host_port[1])
    logging.info('Démarrage ZiGate WiFi {} {}'.format(host, port))
    z = zigate.ZiGateWiFi(host, port, persistent_file, auto_start=False)
else:
    logging.info('Démarrage ZiGate USB {}'.format(args.device))
    z = zigate.ZiGate(args.device, persistent_file, auto_start=False)
zigate.dispatcher.connect(callback_command, zigate.ZIGATE_DEVICE_ADDED, z)
zigate.dispatcher.connect(callback_command, zigate.ZIGATE_DEVICE_UPDATED, z)
zigate.dispatcher.connect(callback_command, zigate.ZIGATE_DEVICE_ADDRESS_CHANGED, z)
zigate.dispatcher.connect(callback_command, zigate.ZIGATE_DEVICE_REMOVED, z)
zigate.dispatcher.connect(callback_command, zigate.ZIGATE_ATTRIBUTE_ADDED, z)
zigate.dispatcher.connect(callback_command, zigate.ZIGATE_ATTRIBUTE_UPDATED, z)
zigate.dispatcher.connect(callback_command, zigate.ZIGATE_DEVICE_NEED_DISCOVERY, z)

z.autoStart(args.channel)
z.start_auto_save()

version = z.get_version_text()
logging.info('Firmware ZiGate : {}'.format(version))
if version < '3.0d':
    logging.error('Veuillez mettre à jour le firmware de votre clé ZiGate')
    logging.error('Version actuelle : {} - Version minimale requise : 3.0d'.format(version))
    sys.exit(1)

if args.sharedata:
    t = threading.Thread(target=sharedata)
    t.setDaemon(True)
    t.start()

t = threading.Thread(target=server.serve_forever)
t.start()
