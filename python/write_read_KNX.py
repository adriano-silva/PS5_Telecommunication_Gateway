#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
import socket
import time
import getopt
import struct

from knxnet import *

__author__ = "Adrien Lescourt"
__copyright__ = "HES-SO 2015, Project EMG4B"
__credits__ = ["Adrien Lescourt", "Adriano Silva"]
__version__ = "1.2.0"
__email__ = "adrien.lescourt@gmail.com"
__status__ = "Prototype"


udp_ip = "129.194.184.31"
udp_port = 3671

sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
sock.bind(('', 3671))

lAddress = ''
cmd = ''
value = ''

def main(argv):
	   #this try to get the execution parameters, they must be one of those letters (l,c,v,h)
       try:
          opts, args = getopt.getopt(argv,"l:c:v:h",["a=","cmd=","val="])
       except getopt.GetoptError:
          print ('error usage: write_read_KNX.py -l <logicalAddress> -c <command> -v <value>')
          sys.exit(2)
	   # loop for each parameter
       for opt, arg in opts:
          if opt == '-h':
             print ('usage: write_read_KNX.py -l <logicalAddress> -c <command> -v <value>')
             sys.exit()
          elif opt in ("-l"):
             lAddress = arg
          elif opt in ("-c"):
             cmd = arg
          elif opt in ("-v"):
             value = int(arg)


       if cmd=='read':
          read_data_to_group_addr(lAddress, 0, 1, 0x0)
       elif cmd=='write':
          length_value= len(str(value))
          write_data_to_group_addr(lAddress, value, length_value)

# Function to write data to a group address
def write_data_to_group_addr(dest_group_addr, data, data_size):
    data_endpoint = ('0.0.0.0', 0)
    control_enpoint = ('0.0.0.0', 0)

    # CONNECTION REQUEST
    conn_req = knxnet.create_frame(knxnet.ServiceTypeDescriptor.CONNECTION_REQUEST,
                                   control_enpoint,
                                   data_endpoint)
    #print('==> Send connection request to {0}:{1}'.format(udp_ip, udp_port))
    #print(repr(conn_req))
    #print(conn_req)
    sock.sendto(conn_req.frame, (udp_ip, udp_port))

    # CONNECTION RESPONSE
    data_recv, addr = sock.recvfrom(3671)
    conn_resp = knxnet.decode_frame(data_recv)
    #print('<== Received connection response:')
    #print(repr(conn_resp))
    #print(conn_resp)

    # CONNECTION STATE REQUEST
    conn_state_req = knxnet.create_frame(knxnet.ServiceTypeDescriptor.CONNECTION_STATE_REQUEST,
                                         conn_resp.channel_id,
                                         control_enpoint)
    #print('==> Send connection state request to channel {0}'.format(conn_resp.channel_id))
    #print(repr(conn_state_req))
    #print(conn_state_req)
    sock.sendto(conn_state_req.frame, (udp_ip, udp_port))

    # CONNECTION STATE RESPONSE
    data_recv, addr = sock.recvfrom(3671)
    conn_state_resp = knxnet.decode_frame(data_recv)
    #print('<== Received connection state response:')
    #print(repr(conn_state_resp))
    #print(conn_state_resp)

    # TUNNEL REQUEST
    tunnel_req = knxnet.create_frame(knxnet.ServiceTypeDescriptor.TUNNELLING_REQUEST,
                                     dest_group_addr,
                                     conn_resp.channel_id,
                                     data,
                                     data_size)

    #print('==> Send tunnelling request to {0}:{1}'.format(udp_ip, udp_port))
    #print(repr(tunnel_req))
    #print(tunnel_req)
    sock.sendto(tunnel_req.frame, (udp_ip, udp_port))

    # TUNNEL ACK
    data_recv, addr = sock.recvfrom(3671)
    ack = knxnet.decode_frame(data_recv)
    #print('<== Received tunnelling ack:')
    #print(repr(ack))
    #print(ack)

    # DISCONNECT REQUEST
    disconnect_req = knxnet.create_frame(knxnet.ServiceTypeDescriptor.DISCONNECT_REQUEST,
                                         conn_resp.channel_id,
                                         control_enpoint)
    #print('==> Send disconnect request to channel {0}'.format(conn_resp.channel_id))
    #print(repr(disconnect_req))
    #print(disconnect_req)
    sock.sendto(disconnect_req.frame, (udp_ip, udp_port))

    # DISCONNECT RESPONSE
    data_recv, addr = sock.recvfrom(3671)
    disconnect_resp = knxnet.decode_frame(data_recv)
    #print('<== Received connection state response:')
    #print(repr(disconnect_resp))
    #print(disconnect_resp)

def read_data_to_group_addr(dest_group_addr, data, data_size, acpi):
    data_endpoint = ('0.0.0.0', 0)
    control_enpoint = ('0.0.0.0', 0)

    # CONNECTION REQUEST
    conn_req = knxnet.create_frame(knxnet.ServiceTypeDescriptor.CONNECTION_REQUEST,
                                   control_enpoint,
                                   data_endpoint)
    #print('==> Send connection request to {0}:{1}'.format(udp_ip, udp_port))
    #print(repr(conn_req))
    #print(conn_req)
    sock.sendto(conn_req.frame, (udp_ip, udp_port))

    # CONNECTION RESPONSE
    data_recv, addr = sock.recvfrom(3671)
    conn_resp = knxnet.decode_frame(data_recv)
    #print('<== Received connection response:')
    #print(repr(conn_resp))
    #print(conn_resp)

    # CONNECTION STATE REQUEST
    conn_state_req = knxnet.create_frame(knxnet.ServiceTypeDescriptor.CONNECTION_STATE_REQUEST,
                                         conn_resp.channel_id,
                                         control_enpoint)
    #print('==> Send connection state request to channel {0}'.format(conn_resp.channel_id))
    #print(repr(conn_state_req))
    #print(conn_state_req)
    sock.sendto(conn_state_req.frame, (udp_ip, udp_port))

    # CONNECTION STATE RESPONSE
    data_recv, addr = sock.recvfrom(3671)
    conn_state_resp = knxnet.decode_frame(data_recv)
    #print('<== Received connection state response:')
    #print(repr(conn_state_resp))
    #print(conn_state_resp)

    # TUNNEL REQUEST
    tunnel_req = knxnet.create_frame(knxnet.ServiceTypeDescriptor.TUNNELLING_REQUEST,
                                     dest_group_addr,
                                     conn_resp.channel_id,
                                     data,
                                     data_size,
                                     acpi)

    #print('==> Send tunnelling request to {0}:{1}'.format(udp_ip, udp_port))
    #print(repr(tunnel_req))
    #print(tunnel_req)
    sock.sendto(tunnel_req.frame, (udp_ip, udp_port))

    # TUNNEL ACK
    data_recv, addr = sock.recvfrom(3671)
    ack = knxnet.decode_frame(data_recv)
    #print('<== Received tunnelling ack:')
    #print(repr(ack))
    #print(ack)

    # TUNNEL REQUEST FROM GATEWAY
    data_recv, addr = sock.recvfrom(3671)
    ack = knxnet.decode_frame(data_recv)
    #print('<== Received tunnelling request from gateway:')
    #print(ack.data)
    #print(ack)

    # TUNNEL ACK TO GATEWAY
    tunnel_ack = knxnet.create_frame(knxnet.ServiceTypeDescriptor.TUNNELLING_ACK,
                                     conn_resp.channel_id,
                                     0,
                                     ack.sequence_counter)

    #print('==> Send tunnelling ack to {0}:{1}'.format(udp_ip, udp_port))
    #print(tunnel_ack)
    sock.sendto(tunnel_ack.frame, (udp_ip, udp_port))

    # TUNNEL REQUEST FROM GATEWAY WITH READ VALUE
    data_recv, addr = sock.recvfrom(3671)
    ack = knxnet.decode_frame(data_recv)
    data = str(ack.data)
    fdata=0
    if data!="0000":
        fdata = struct.unpack('!f', bytes.fromhex(data))
        print ("%.4f" % fdata[0])
    else:
        print("0")
    #print('<== Received tunnelling request from gateway with readed value:')
    #print("\tRaw read value : "+ack.data)
    #print("\t Value : "+ str(fdata[0]))
    #print("Datapoint : "+ str(ack.data_service))

    #print(ack)

    # DISCONNECT REQUEST
    disconnect_req = knxnet.create_frame(knxnet.ServiceTypeDescriptor.DISCONNECT_REQUEST,
                                         conn_resp.channel_id,
                                         control_enpoint)
    #print('==> Send disconnect request to channel {0}'.format(conn_resp.channel_id))
    #print(repr(disconnect_req))
    #print(disconnect_req)
    sock.sendto(disconnect_req.frame, (udp_ip, udp_port))

    # DISCONNECT RESPONSE
    data_recv, addr = sock.recvfrom(3671)
    disconnect_resp = knxnet.decode_frame(data_recv)
    #print('<== Received connection state response:')
    #print(repr(disconnect_resp))
    #print(disconnect_resp)


if __name__ == '__main__':
   main(sys.argv[1:])
   dest = knxnet.GroupAddress.from_str('1/1/0')
   #write_data_to_group_addr('1/0/0', 1, 1)
   #print('Waiting 5 seconds...')
   #time.sleep(5)
   #write_data_to_group_addr('1/0/0', 0, 1)
