# IoTAttendanceSystem

A biometric attendance system using **Raspberry Pi Pico (base)**, **ENC28J60** Ethernet module, **R307** fingerprint sensor, and a **PHP server** with MySQL database. Designed for **MIL-STD-810G** security in a fully dynamic network (no fixed IPs/MACs, no broadcasts, no pre-selected IPs, no dummy packets, firewall-friendly, untouchable PHP/MicroPython code, no network settings access). Supports fingerprint enrollment, verification, and attendance logging with secure data storage for multi-system sharing. Pico IPs are manually provided to the server, avoiding network discovery.

## Table of Contents
1. [System Overview](#system-overview)
2. [Hardware Requirements](#hardware-requirements)
3. [Circuit Schematic](#circuit-schematic)
4. [Software Requirements](#software-requirements)
5. [Installation](#installation)
6. [Configuration](#configuration)
7. [Usage](#usage)
8. [Security Features](#security-features)
9. [Database Structure](#database-structure)
10. [Troubleshooting](#troubleshooting)
11. [License](#license)

## System Overview
- **Purpose**: Securely tracks attendance using biometric fingerprint data, storing logs and templates in a MySQL database for access across multiple systems.
- **Raspberry Pi Pico**: Manages R307 fingerprint data processing and communicates with the PHP server via ENC28J60 using MicroPython.
- **R307 Fingerprint Sensor**: Captures and verifies fingerprints, supports up to 1000 templates, and interfaces over UART.
- **ENC28J60 Ethernet Module**: Enables Ethernet-based HTTPS communication, ensuring compatibility with network firewalls.
- **PHP Server**: Receives encrypted fingerprint data via HTTPS POST, stores it in MySQL, and supports manual Pico IP input with admin access.

## Hardware Requirements
- **Raspberry Pi Pico (base)**: RP2040 microcontroller, operates at 3.3V logic, lacks built-in Wi-Fi.
- **ENC28J60 Ethernet Module**: SPI-based Ethernet interface, 3.3V, consumes ~180mA, includes RJ45 port for LAN connectivity.
- **R307 Fingerprint Sensor**: Optical fingerprint sensor, UART interface, 3.3V/5V compatible, ~50mA draw, 1000-fingerprint capacity.
- **Power Supply**: Stable 3.3V source with at least 250mA capacity; external regulator recommended to handle combined draw.
- **Additional Components**: Push button for enrollment, 10kΩ pull-down resistor, jumper wires, and an Ethernet cable.

## Circuit Schematic
1. **Raspberry Pi Pico**
    - Pin 36 (3V3) --------> [ENC28J60 VCC]
    - Pin 36 (3V3) --------> [R307 VCC]
    - Pin 3 (GND) ---------> [ENC28J60 GND]
    - Pin 3 (GND) ---------> [R307 GND]
    - Pin 3 (GND) ---------> [Button GND]
    - Pin 1 (GP0/TX) ------> [R307 RX]
    - Pin 2 (GP1/RX) ------> [R307 TX]
    - Pin 24 (GP18/SCK) ---> [ENC28J60 SCK]
    - Pin 21 (GP16/MISO) --> [ENC28J60 MISO]
    - Pin 25 (GP19/MOSI) --> [ENC28J60 MOSI]
    - Pin 22 (GP17/CS) ----> [ENC28J60 CS]
    - Pin 20 (GP15) -------> [Button]
2. **Button** -------------> [10kΩ Pull-Down Resistor] ----> [GND]
3. **ENC28J60 RJ45** ------> [LAN Router/Switch]

- **Description**: The Raspberry Pi Pico supplies 3.3V power (Pin 36) to both the ENC28J60 Ethernet module and R307 fingerprint sensor, with a shared ground connection (Pin 3). The R307 interfaces via UART, connecting GP0 (TX, Pin 1) to R307 RX and GP1 (RX, Pin 2) to R307 TX. The ENC28J60 connects via SPI, with GP18 (SCK, Pin 24) to SCK, GP16 (MISO, Pin 21) to MISO, GP19 (MOSI, Pin 25) to MOSI, and GP17 (CS, Pin 22) to CS. A push button connects to GP15 (Pin 20) and GND through a 10kΩ pull-down resistor to trigger enrollment mode. The ENC28J60’s RJ45 port connects to a LAN router or switch. Ensure a stable 3.3V power supply to handle the combined ~230mA draw. Convert this schematic to a diagram using Fritzing or CircuitLab for visualization.

## Software Requirements
- **MicroPython**: Firmware for Pico (version 1.20 or higher), including `urequests`, `ussl`, and `network` modules for HTTPS communication.
- **PHP**: Server-side environment, PHP 7.4 or higher, with `curl`, `mysqli`, and HTTPS support for secure data handling.
- **MySQL**: Database server (MariaDB or MySQL 5.7+) to store user and attendance data securely.
- **ENC28J60 Driver**: MicroPython `network.LAN` driver or equivalent (e.g., `micropython-enc28j60`) for Ethernet connectivity.
- **SSL Certificates**: Required for HTTPS communication; self-signed certificates for testing, trusted CA certificates for production.

## Installation
1. **Pico Setup**:
   - Flash MicroPython firmware to the Pico using Thonny, VS Code, or `esptool`.
   - Save the MicroPython script (provided separately) to the Pico’s filesystem as `main.py`.
   - Connect the R307 fingerprint sensor: VCC to Pin 36 (3V3), GND to Pin 3, TX to GP1 (Pin 2), RX to GP0 (Pin 1).
   - Connect the ENC28J60 Ethernet module: VCC to Pin 36, GND to Pin 3, SCK to GP18 (Pin 24), MISO to GP16 (Pin 21), MOSI to GP19 (Pin 25), CS to GP17 (Pin 22).
   - Attach a push button between GP15 (Pin 20) and GND, with a 10kΩ pull-down resistor to GND.

2. **PHP Server Setup**:
   - Install PHP 7.4+, MySQL, and a web server (e.g., Apache or Nginx) configured for HTTPS with SSL/TLS.
   - Copy the PHP files (`config.php`, `log.php`, `admin.php`, provided separately) to the web server’s root directory.
   - Create a MySQL database named `attendance_db` and set up the tables as specified in the Database Structure section.
   - Set file permissions: `config.php` writable by admin, read-only by server; `log.php` and `admin.php` executable by server.
   - Update `config.php` with MySQL credentials and manually enter Pico IPs obtained from your network (e.g., router DHCP logs).

3. **Network**:
   - Connect the ENC28J60 module to a LAN router or switch using an Ethernet cable.
   - Ensure the PHP server is accessible via HTTPS on port 443, with a valid SSL certificate.
   - Manually obtain Pico IPs from your network (e.g., router interface or DHCP logs) and add them to `config.php`.
   - Verify network connectivity by pinging the server from a test device.
   - Ensure firewall allows HTTPS traffic (port 443) for Pico-to-server communication.

## Configuration
- **Pico**:
   - Update the `SERVER_IP` variable in the MicroPython script with the PHP server’s current IP address, obtained manually (e.g., from server logs or network tools).
   - Set the `PRESHARED_KEY` to a secure, unique value (at least 32 characters), ensuring it matches the server’s key.
   - Configure `SSL_PARAMS` with proper SSL certificates in production; use self-signed certificates for testing only.
   - Optionally, store the server IP in a separate config file on the Pico’s filesystem to simplify updates without reflashing.
   - Test the push button on GP15 to confirm enrollment mode activation.

- **PHP Server**:
   - Edit `config.php`:
     - Set `DB_HOST` (e.g., `localhost`), `DB_USER`, `DB_PASS`, and `DB_NAME` (`attendance_db`) for MySQL access.
     - Add Pico IPs to the `$pico_ips` array (e.g., `192.168.1.101`, `192.168.1.102`), manually obtained from your network.
     - Set `PRESHARED_KEY` to match the Pico’s key for secure authentication.
   - Secure the `admin.php` interface with strong authentication (e.g., OAuth, password hashing) to protect IP updates.
   - If the server’s IP changes, manually update the Pico’s MicroPython script and ensure HTTPS certificates remain valid.

## Usage
1. **Pico Operation**:
   - Power on the Pico; the ENC28J60 connects to the network via DHCP automatically.
   - Place a finger on the R307 sensor to verify a fingerprint; the Pico sends the fingerprint ID to the server for attendance logging.
   - Press the GP15 button to enter enrollment mode, then scan a new fingerprint to enroll a user; the template is sent to the server.
   - Fingerprint data (IDs or templates) is transmitted via secure HTTPS POST requests to the PHP server.
   - Monitor the Pico’s serial console (via Thonny or a serial terminal) for status messages (e.g., “Fingerprint ID: 1”, “Sending template”).

2. **PHP Server**:
   - The server’s `/log.php` endpoint receives encrypted HTTPS POST requests, validates them, and stores data in the MySQL database.
   - Use `/admin.php` to update Pico IPs manually (access requires secure admin authentication).
   - View attendance logs and user data through a separate admin interface (not included; develop as needed).
   - The server only accepts requests from whitelisted Pico IPs and valid pre-shared keys.
   - Data is stored securely in the database, enabling sharing across multiple systems.

## Security Features
- **HTTPS Communication**: All Pico-to-server communication uses SSL/TLS on port 443, ensuring encryption and firewall compatibility.
- **Pre-Shared Key Authentication**: A unique key (32+ characters) validates all requests, preventing unauthorized access.
- **XOR Payload Encryption**: Fingerprint data is XOR-encrypted with the pre-shared key before transmission, adding an extra security layer.
- **IP Whitelisting**: The server only processes requests from manually configured Pico IPs, reducing attack surfaces.
- **MIL-STD-810G Compliance**: Localhost-only MySQL access, secure HTTP headers, and encrypted communication meet security standards.

## Database Structure
- **users Table**:
  - `id`: Auto-incrementing primary key (INT).
  - `fingerprint_id`: Unique fingerprint ID (INT, NOT NULL).
  - `template`: Fingerprint template data (BLOB, NOT NULL).
  - `created_at`: Timestamp of record creation (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP).
  - Stores user fingerprint templates for verification and enrollment.

- **attendance Table**:
  - `id`: Auto-incrementing primary key (INT).
  - `fingerprint_id`: Associated fingerprint ID (INT, NOT NULL).
  - `timestamp`: Attendance log time (DATETIME, NOT NULL).
  - `created_at`: Timestamp of record creation (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP).
  - Logs attendance events with fingerprint IDs and timestamps.

- **Access**: Configured for localhost-only connections to prevent external access.
- **Security**: Use read-only queries for data retrieval and secure credentials.
- **Scalability**: Supports multiple Picos and users with efficient indexing.

## Troubleshooting
- **Pico Not Connecting**: Verify ENC28J60 wiring, Ethernet cable, and LAN router connectivity; check DHCP assignment in router logs.
- **R307 Not Responding**: Confirm UART connections (GP0/GP1), ensure 3.3V power, test with a serial console to debug sensor commands.
- **Server Errors**: Check HTTPS configuration, SSL certificate validity, `config.php` permissions, and MySQL credentials; review server logs for errors.
- **Data Not Received**: Ensure `SERVER_IP` in `main.py` matches the server’s current IP, `PRESHARED_KEY` is identical, and Pico IPs are in `config.php`.
- **Security Issues**: Monitor server logs for unauthorized POST attempts; rotate pre-shared keys and update SSL certificates if compromised.

## License
This project is licensed under the **MIT License**. Permission is granted, free of charge, to any person obtaining a copy to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the software, subject to including the original copyright notice and permission notice in all copies or substantial portions. The software is provided "as is", without warranty of any kind. See the [LICENSE](LICENSE) file for details.

---
This README provides comprehensive documentation for the IoTAttendanceSystem, detailing hardware, software, installation, configuration, usage, security, and troubleshooting for deployment in a environment.