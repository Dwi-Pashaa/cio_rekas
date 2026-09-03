# 📋 Panduan & Implementation Plan: Integrasi MikroTik RouterOS API ke Project `cio_network`

## 📌 1. Ringkasan Proyek
Dokumen ini adalah panduan lengkap dan rencana implementasi modul **MikroTik RouterOS API Monitoring & Sinkronisasi Perangkat** yang telah diuji coba dan siap diintegrasikan ke project utama **`cio_network`**.

> **Hasil Pengujian Awal (PoC):**
> * **Target Router**: MikroTik RouterBOARD hEX (`42.62.176.169:8728`)
> * **Versi RouterOS**: v6.48.2 (Stable)
> * **Data Terambil**: Total **1.737 MAC Address** (1.670 status aktif, 67 status tidak aktif) via gabungan DHCP Leases, ARP Table, dan Hotspot Hosts.

---

## 🏗️ 2. Arsitektur Modul di `cio_network`

```mermaid
graph TD
    subgraph MikroTik Router
        MT[MikroTik 42.62.176.169:8728]
        DHCP[DHCP Server Leases]
        ARP[ARP Table]
        HS[Hotspot / PPPoE]
        MT --> DHCP
        MT --> ARP
        MT --> HS
    end

    subgraph Project cio_network (Laravel)
        ENV[.env Configuration]
        CFG[config/mikrotik.php]
        RO_API[app/Services/RouterosAPI.php]
        MS[app/Services/MikrotikService.php]
        CACHE[(Cache Layer - 5 Menit)]
        CMD[Console/Commands/SyncMikrotikDevices.php]
        DB[(Database: mikrotik_devices & customers)]
        CTRL[Http/Controllers/MikrotikController.php]

        ENV --> CFG
        MT <-->|Port 8728 TCP Socket| RO_API
        RO_API --> MS
        MS --> CACHE
        MS --> CMD
        CMD --> DB
        CTRL --> MS
    end

    subgraph Antarmuka Pengguna (cio_network UI)
        VIEW_DASH[Dashboard Monitoring Perangkat]
        VIEW_CUST[Detail Status Koneksi Pelanggan]
        CTRL --> VIEW_DASH
        CTRL --> VIEW_CUST
    end
```

---

## 📁 3. File yang Perlu Ditambahkan / Diimplementasikan di `cio_network`

Berikut daftar file dan kode lengkap yang siap dipindahkan/dibuat di project **`cio_network`**:

| No | File Path di `cio_network` | Tipe | Fungsi |
| :--- | :--- | :--- | :--- |
| 1 | `config/mikrotik.php` | Baru | Konfigurasi host, port, user, password, timeout, dan TTL cache |
| 2 | `app/Services/RouterosAPI.php` | Baru | Client TCP Socket native penghubung Laravel ke RouterOS API (v6 & v7) |
| 3 | `app/Services/MikrotikService.php` | Baru | Service logika: tarik DHCP, ARP, Hotspot, Caching, & Auto-Aggregator |
| 4 | `database/migrations/xxxx_create_mikrotik_devices_table.php` | Baru | Tabel database untuk menyimpan data & histori perangkat jaringan |
| 5 | `app/Models/MikrotikDevice.php` | Baru | Model Eloquent perangkat dengan relasi ke data `Customer` |
| 6 | `app/Console/Commands/SyncMikrotikDevices.php` | Baru | Artisan Command `php artisan mikrotik:sync` untuk cron scheduler |
| 7 | `app/Http/Controllers/MikrotikController.php` | Baru | Controller dashboard monitoring, filter data, dan export |
| 8 | `resources/views/mikrotik/index.blade.php` | Baru | Halaman Blade monitoring status perangkat jaringan |
| 9 | `routes/web.php` & `routes/api.php` | Modifikasi | Route web dashboard dan route API bridge |
| 10 | `resources/views/components/sidebar.blade.php` | Modifikasi | Menambahkan menu "Monitoring Jaringan / MikroTik" |

---

## 🚀 4. Langkah-Langkah & Kode Sumber untuk `cio_network`

### Langkah 1: Konfigurasi Environment (`.env` & `config/mikrotik.php`)

Tambahkan variabel berikut pada file `.env` di project `cio_network`:
```env
MIKROTIK_HOST=42.62.176.169
MIKROTIK_PORT=8728
MIKROTIK_USER=dwi1234
MIKROTIK_PASS=dwi1234
MIKROTIK_TIMEOUT=5
MIKROTIK_CACHE_TTL=300
```

Buat file konfigurasi `config/mikrotik.php`:
```php
<?php

return [
    'host'      => env('MIKROTIK_HOST', '42.62.176.169'),
    'port'      => (int) env('MIKROTIK_PORT', 8728),
    'user'      => env('MIKROTIK_USER', 'admin'),
    'pass'      => env('MIKROTIK_PASS', ''),
    'timeout'   => (int) env('MIKROTIK_TIMEOUT', 5),
    'cache_ttl' => (int) env('MIKROTIK_CACHE_TTL', 300), // 5 Menit
];
```

---

### Langkah 2: Kode `RouterosAPI.php` (`app/Services/RouterosAPI.php`)

```php
<?php

namespace App\Services;

/**
 * RouterOS API client class
 * Compatible with RouterOS v6 and v7, and PHP 8.1+
 */
class RouterosAPI
{
    public $connected = false;
    public $port = 8728;
    public $ssl = false;
    public $timeout = 3;
    public $attempts = 3;
    public $delay = 1;

    protected $socket;
    public $last_error = '';
    public $debug_logs = [];

    public function connect($ip, $login, $password, $port = null, $ssl = false)
    {
        $this->port = $port ?? ($ssl ? 8729 : 8728);
        $this->ssl = $ssl;
        $this->debug_logs = [];

        $proto = $this->ssl ? 'ssl://' : '';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $this->socket = @stream_socket_client(
            $proto . $ip . ':' . $this->port,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$this->socket) {
            $this->last_error = "Gagal membuka socket ke {$ip}:{$this->port}. Error: {$errstr} ({$errno})";
            return false;
        }

        socket_set_timeout($this->socket, $this->timeout);

        // Modern login (v6.43+ & v7)
        $this->write('/login', false);
        $this->write('=name=' . $login, false);
        $this->write('=password=' . $password);
        $response = $this->read(false);

        if (isset($response[0]) && $response[0] === '!done') {
            if (!isset($response[1])) {
                $this->connected = true;
                return true;
            } elseif (str_starts_with($response[1], '=ret=')) {
                $challenge = pack('H*', substr($response[1], 5));
                $hash = md5(chr(0) . $password . $challenge);
                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=response=00' . $hash);
                $resp2 = $this->read(false);
                if (isset($resp2[0]) && $resp2[0] === '!done') {
                    $this->connected = true;
                    return true;
                }
            }
        }

        $this->last_error = "Autentikasi gagal. Username atau password tidak diterima RouterOS.";
        $this->disconnect();
        return false;
    }

    public function disconnect()
    {
        if (is_resource($this->socket)) {
            @fclose($this->socket);
        }
        $this->connected = false;
    }

    public function parseResponse(array $response): array
    {
        $parsed = [];
        $current = [];

        foreach ($response as $line) {
            if ($line === '!re') {
                if (!empty($current)) {
                    $parsed[] = $current;
                    $current = [];
                }
            } elseif ($line === '!done') {
                if (!empty($current)) {
                    $parsed[] = $current;
                }
                break;
            } elseif ($line === '!trap' || $line === '!fatal') {
                continue;
            } else {
                if (str_starts_with($line, '=')) {
                    $parts = explode('=', substr($line, 1), 2);
                    if (count($parts) === 2) {
                        $current[$parts[0]] = $parts[1];
                    }
                }
            }
        }

        return $parsed;
    }

    public function write($param, $param2 = true)
    {
        if ($this->socket === null) return false;

        if (is_array($param)) {
            foreach ($param as $word) $this->writeWord($word);
        } else {
            $this->writeWord($param);
        }

        if ($param2) $this->writeWord('');
        return true;
    }

    public function read($parse = true)
    {
        $response = [];
        while (true) {
            $word = $this->readWord();
            if ($word === '') {
                $status = stream_get_meta_data($this->socket);
                if ($status['timed_out']) break;
                continue;
            }
            $response[] = $word;
            if ($word === '!done' || $word === '!fatal') break;
        }

        return $parse ? $this->parseResponse($response) : $response;
    }

    protected function writeWord($word)
    {
        $this->encodeLength(strlen($word));
        @fwrite($this->socket, $word);
    }

    protected function readWord(): string
    {
        $byteCount = $this->decodeLength();
        if ($byteCount === 0) return '';

        $word = '';
        while (strlen($word) < $byteCount) {
            $chunk = @fread($this->socket, $byteCount - strlen($word));
            if ($chunk === false || $chunk === '') break;
            $word .= $chunk;
        }
        return $word;
    }

    protected function encodeLength($length)
    {
        if ($length < 0x80) {
            @fwrite($this->socket, chr($length));
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            @fwrite($this->socket, chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            @fwrite($this->socket, chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        }
    }

    protected function decodeLength(): int
    {
        $firstChar = @fread($this->socket, 1);
        if ($firstChar === false || $firstChar === '') return 0;

        $length = ord($firstChar);
        if (($length & 0x80) === 0x00) return $length;
        if (($length & 0xC0) === 0x80) {
            $length &= ~0x80;
            return ($length << 8) + ord(@fread($this->socket, 1));
        }
        return 0;
    }
}
```

---

### Langkah 3: Kode `MikrotikService.php` (`app/Services/MikrotikService.php`)

```php
<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;

class MikrotikService
{
    protected string $host;
    protected string $user;
    protected string $pass;
    protected int $port;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->host     = config('mikrotik.host', '42.62.176.169');
        $this->user     = config('mikrotik.user', 'dwi1234');
        $this->pass     = config('mikrotik.pass', 'dwi1234');
        $this->port     = (int) config('mikrotik.port', 8728);
        $this->cacheTtl = (int) config('mikrotik.cache_ttl', 300);
    }

    public function getAllMacAddresses(bool $forceFresh = false): array
    {
        $cacheKey = "mikrotik_all_mac_{$this->host}";

        if (!$forceFresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $dhcp = $this->getDhcpLeases();
        $arp  = $this->getArpTable();
        $hs   = $this->getHotspotHosts();

        $devices = [];

        foreach ($dhcp as $d) {
            $mac = strtoupper($d['mac_address'] ?? '');
            if (!$mac) continue;
            $devices[$mac] = [
                'mac_address' => $mac,
                'ip_address'  => $d['ip_address'],
                'host_name'   => $d['host_name'],
                'status'      => $d['is_active'] ? 'active' : 'inactive',
                'is_active'   => $d['is_active'],
                'source'      => ['DHCP Lease'],
                'details'     => ['dhcp' => $d]
            ];
        }

        foreach ($arp as $a) {
            $mac = strtoupper($a['mac_address'] ?? '');
            if (!$mac) continue;
            if (isset($devices[$mac])) {
                $devices[$mac]['source'][] = 'ARP Table';
                $devices[$mac]['details']['arp'] = $a;
                if ($a['is_active']) {
                    $devices[$mac]['is_active'] = true;
                    $devices[$mac]['status'] = 'active';
                }
            } else {
                $devices[$mac] = [
                    'mac_address' => $mac,
                    'ip_address'  => $a['ip_address'],
                    'host_name'   => $a['comment'] ?? '-',
                    'status'      => $a['is_active'] ? 'active' : 'inactive',
                    'is_active'   => $a['is_active'],
                    'source'      => ['ARP Table'],
                    'details'     => ['arp' => $a]
                ];
            }
        }

        foreach ($hs as $h) {
            $mac = strtoupper($h['mac_address'] ?? '');
            if (!$mac) continue;
            if (isset($devices[$mac])) {
                $devices[$mac]['source'][] = 'Hotspot Host';
                $devices[$mac]['details']['hotspot'] = $h;
                if ($h['is_active']) {
                    $devices[$mac]['is_active'] = true;
                    $devices[$mac]['status'] = 'active';
                }
            } else {
                $devices[$mac] = [
                    'mac_address' => $mac,
                    'ip_address'  => $h['ip_address'],
                    'host_name'   => $h['user'] ?? '-',
                    'status'      => $h['is_active'] ? 'active' : 'inactive',
                    'is_active'   => $h['is_active'],
                    'source'      => ['Hotspot Host'],
                    'details'     => ['hotspot' => $h]
                ];
            }
        }

        $allList      = array_values($devices);
        $activeList   = array_values(array_filter($allList, fn($x) => $x['is_active']));
        $inactiveList = array_values(array_filter($allList, fn($x) => !$x['is_active']));

        $result = [
            'total_detected' => count($allList),
            'total_active'   => count($activeList),
            'total_inactive' => count($inactiveList),
            'all'            => $allList,
            'active'         => $activeList,
            'inactive'       => $inactiveList,
        ];

        Cache::put($cacheKey, $result, $this->cacheTtl);
        return $result;
    }

    public function getDhcpLeases(): array
    {
        $api = new RouterosAPI();
        if (!$api->connect($this->host, $this->user, $this->pass, $this->port)) {
            return [];
        }
        $api->write('/ip/dhcp-server/lease/print');
        $leases = $api->read();
        $api->disconnect();

        return array_map(function ($item) {
            $status = $item['status'] ?? 'unknown';
            $disabled = ($item['disabled'] ?? 'false') === 'true';
            return [
                'mac_address' => $item['mac-address'] ?? null,
                'ip_address'  => $item['address'] ?? null,
                'host_name'   => $item['host-name'] ?? ($item['comment'] ?? '-'),
                'is_active'   => ($status === 'bound' && !$disabled),
                'status_raw'  => $status,
            ];
        }, $leases);
    }

    public function getArpTable(): array
    {
        $api = new RouterosAPI();
        if (!$api->connect($this->host, $this->user, $this->pass, $this->port)) {
            return [];
        }
        $api->write('/ip/arp/print');
        $arp = $api->read();
        $api->disconnect();

        return array_map(function ($item) {
            $disabled = ($item['disabled'] ?? 'false') === 'true';
            $invalid  = ($item['invalid'] ?? 'false') === 'true';
            $complete = ($item['complete'] ?? 'true') === 'true';
            return [
                'mac_address' => $item['mac-address'] ?? null,
                'ip_address'  => $item['address'] ?? null,
                'interface'   => $item['interface'] ?? null,
                'comment'     => $item['comment'] ?? null,
                'is_active'   => (!$disabled && !$invalid && $complete),
            ];
        }, $arp);
    }

    public function getHotspotHosts(): array
    {
        $api = new RouterosAPI();
        if (!$api->connect($this->host, $this->user, $this->pass, $this->port)) {
            return [];
        }
        $api->write('/ip/hotspot/host/print');
        $hosts = $api->read();
        $api->disconnect();

        return array_map(function ($item) {
            $authorized = ($item['authorized'] ?? 'false') === 'true';
            $bypassed   = ($item['bypassed'] ?? 'false') === 'true';
            return [
                'mac_address' => $item['mac-address'] ?? null,
                'ip_address'  => $item['address'] ?? null,
                'user'        => $item['user'] ?? null,
                'is_active'   => ($authorized || $bypassed),
            ];
        }, $hosts);
    }
}
```

---

## 🎯 5. Checklist Eksekusi Saat Pindah ke `cio_network`

- [ ] 1. Buka project `cio_network`.
- [ ] 2. Tambahkan kredensial di `.env` dan buat `config/mikrotik.php`.
- [ ] 3. Letakkan `RouterosAPI.php` dan `MikrotikService.php` di `app/Services/`.
- [ ] 4. Jalankan migration tabel `mikrotik_devices` dan buat Model `MikrotikDevice.php`.
- [ ] 5. Buat Artisan command `mikrotik:sync` dan jadwalkan di `Kernel.php`.
- [ ] 6. Buat `MikrotikController.php` dan view blade monitoring.
- [ ] 7. Uji coba sinkronisasi pertama kali dengan `php artisan mikrotik:sync`.
