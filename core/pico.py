from machine import UART, Pin, SPI
import time
import urequests
import ujson
import binascii
import ussl
import network

# R307 UART setup (GP0 TX, GP1 RX)
uart = UART(0, baudrate=57600, tx=Pin(0), rx=Pin(1), bits=8, parity=None, stop=1)

# ENC28J60 SPI setup
spi = SPI(0, baudrate=10000000, sck=Pin(18), mosi=Pin(19), miso=Pin(16))
cs = Pin(17, Pin.OUT)

# Configuration
SERVER_IP = "192.168.1.100"  # Manually update with server’s dynamic IP
PRESHARED_KEY = "MILITARY_GRADE_KEY_1234567890"
SSL_PARAMS = {"cert_reqs": ussl.CERT_NONE}  # Use proper certs in production

def init_enc28j60():
    cs.value(1)
    time.sleep_ms(100)
    cs.value(0)
    try:
        nic = network.LAN(spi=spi, cs=cs)
        nic.active(True)
        nic.ifconfig('dhcp')
        time.sleep(5)  # Wait for DHCP
        return nic.isconnected()
    except Exception as e:
        print("ENC28J60 init failed:", e)
        return False

def xor_encrypt(data):
    key = PRESHARED_KEY
    return ''.join(chr(ord(c) ^ ord(key[i % len(key)])) for i, c in enumerate(data))

def calculate_checksum(cmd):
    total = sum(cmd[6:])
    return total.to_bytes(2, 'big')

def send_r307_command(cmd):
    cmd = bytearray(cmd)
    cmd += calculate_checksum(cmd)
    uart.write(cmd)
    time.sleep_ms(100)
    response = uart.read()
    return response

def verify_fingerprint():
    # GetImage
    cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x03, 0x01, 0x00]
    response = send_r307_command(cmd)
    if response and len(response) > 9 and response[9] == 0x00:
        # Image2Tz (buffer 1)
        cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x04, 0x02, 0x01, 0x00]
        response = send_r307_command(cmd)
        if response and len(response) > 9 and response[9] == 0x00:
            # Search
            cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x08, 0x04, 0x01, 0x00, 0x00, 0xFF, 0xFF, 0x01]
            response = send_r307_command(cmd)
            if response and len(response) > 11 and response[9] == 0x00:
                return (response[10] << 8) + response[11]
    return None

def enroll_fingerprint(fingerprint_id):
    # GetImage
    cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x03, 0x01, 0x00]
    if send_r307_command(cmd)[9] == 0x00:
        # Image2Tz (buffer 1)
        cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x04, 0x02, 0x01, 0x00]
        if send_r307_command(cmd)[9] == 0x00:
            time.sleep(1)
            # GetImage again
            cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x03, 0x01, 0x00]
            if send_r307_command(cmd)[9] == 0x00:
                # Image2Tz (buffer 2)
                cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x04, 0x02, 0x02, 0x00]
                if send_r307_command(cmd)[9] == 0x00:
                    # RegModel
                    cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x03, 0x05, 0x00]
                    if send_r307_command(cmd)[9] == 0x00:
                        # Store
                        cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x06, 0x06, 0x01, fingerprint_id >> 8, fingerprint_id & 0xFF, 0x00]
                        return send_r307_command(cmd)[9] == 0x00
    return False

def get_template(fingerprint_id):
    # Load template
    cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x05, 0x07, 0x01, fingerprint_id >> 8, fingerprint_id & 0xFF]
    if send_r307_command(cmd)[9] == 0x00:
        # DownChar
        cmd = [0xEF, 0x01, 0xFF, 0xFF, 0xFF, 0xFF, 0x01, 0x00, 0x04, 0x08, 0x01, 0x00]
        response = send_r307_command(cmd)
        if response and len(response) > 12 and response[9] == 0x00:
            return response[12:]
    return None

def send_to_server(data_type, data):
    url = f"https://{SERVER_IP}/log.php"
    payload = ujson.dumps({data_type: xor_encrypt(ujson.dumps(data))})
    headers = {"X-Auth": PRESHARED_KEY, "Content-Type": "application/json"}
    try:
        response = urequests.post(url, headers=headers, data=payload, **SSL_PARAMS)
        success = response.status_code == 200
        response.close()
        return success
    except Exception as e:
        print("Send failed:", e)
        return False

# Main loop
if init_enc28j60():
    print("ENC28J60 initialized")
    while True:
        fingerprint_id = verify_fingerprint()
        if fingerprint_id:
            print("Fingerprint ID:", fingerprint_id)
            send_to_server("fingerprint_id", {"id": fingerprint_id, "time": time.time()})
        if Pin(15, Pin.IN, Pin.PULL_DOWN).value():  # Enrollment button
            new_id = 1  # Should be dynamic (e.g., from server or counter)
            print("Enrolling ID:", new_id)
            if enroll_fingerprint(new_id):
                template = get_template(new_id)
                if template:
                    print("Sending template for ID:", new_id)
                    send_to_server("template", {"id": new_id, "template": binascii.hexlify(template).decode()})
        time.sleep(1)
else:
    print("ENC28J60 initialization failed")