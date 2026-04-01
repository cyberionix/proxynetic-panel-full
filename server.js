const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const { exec } = require('child_process');
const os = require('os');

const app = express();
app.use(bodyParser.json());

const CONFIG_FILE = '/etc/3proxy/3proxy.cfg';
const LOG_FILE = '/var/log/3proxy.log';

function getRandomIp() {
    const interfaces = os.networkInterfaces();
    const ipv4Addresses = [];

    for (const iface in interfaces) {
        for (const alias of interfaces[iface]) {
            if (alias.family === 'IPv4' && !alias.internal) {
                ipv4Addresses.push(alias.address);
            }
        }
    }

    if (ipv4Addresses.length === 0) {
        return null;
    }

    return ipv4Addresses[Math.floor(Math.random() * ipv4Addresses.length)];
}

function getRandomPort() {
    return Math.floor(Math.random() * (63000 - 8000 + 1)) + 8000;
}

app.post('/create-proxy', (req, res) => {
    const { port, authUser, authPassword, protocol, limit, apikey, external, allow, deny } = req.body;

    if (apikey != "eBSNhaSfFzzHHSx2E9cJTubNT") {
        return res.status(400).json({ status: 'fail', message: 'Invalid API KEY' });
    }

    let proxyType;
    if (protocol === 'http') {
        proxyType = 'proxy';
    } else if (protocol === 'https') {
        proxyType = 'https';
    } else if (protocol === 'socks') {
        proxyType = 'socks';
    } else {
        return res.status(400).json({ status: 'fail', message: 'Invalid protocol' });
    }

    let newProxy = `\n\nflush\nmaxconn 100\n`;

    if (allow && allow.length > 0) {
        newProxy += `allow * * ${allow.join(',')}\n`;
        newProxy += `deny *\n`;
    } else if (deny && deny.length > 0) {
        newProxy += `deny * * ${deny.join(',')}\n`;
        newProxy += `allow *\n`;
    }

    newProxy += `\nusers ${authUser}:CL:${authPassword}\n`;

    let externalIp;
    if (external) {
        externalIp = external;
    } else {
        externalIp = getRandomIp();
    }

    if (externalIp) {
        newProxy += `external ${externalIp}\n`;
    } else {
        return res.status(500).json({ status: 'fail', message: 'Failed to get external IP address' });
    }

    let portx;
    if (port && port.length > 1) {
        portx = port;
    } else {
        portx = getRandomPort();
    }

    newProxy += `${proxyType} -n -p${portx} -a\n`;
    newProxy += `log /var/log/3proxy.log D\n`; // Enable logging

    fs.appendFile(CONFIG_FILE, newProxy, (err) => {
        if (err) return res.status(500).json({ status: 'fail', message: 'Failed to create proxy' });
        exec('sudo kill -s USR1 $(pidof 3proxy)', (err) => {
            if (err) return res.status(500).json({ status: 'fail', message: 'Failed to reconfigure 3proxy' });
            res.json({ status: 'success', message: 'Proxy created successfully', portx, authUser, authPassword, limit, externalIp, allow, deny });
        });
    });
});

app.post('/update-whitelist', (req, res) => {
    const { authUser, whitelistIp, apikey } = req.body;

    if (apikey != "eBSNhaSfFzzHHSx2E9cJTubNT") {
        return res.status(400).json({ status: 'fail', message: 'Invalid API KEY' });
    }

    if (!authUser || !whitelistIp) {
        return res.status(400).json({ status: 'fail', message: 'Missing authUser or whitelistIp' });
    }

    fs.readFile(CONFIG_FILE, 'utf8', (err, data) => {
        if (err) return res.status(500).json({ status: 'fail', message: 'Failed to read config file' });

        const newData = data.split('\n').map(line => {
            if (line.includes(`allow ${authUser}`)) {
                return `allow ${authUser} ${whitelistIp}`;
            }
            return line;
        }).join('\n');

        fs.writeFile(CONFIG_FILE, newData, (err) => {
            if (err) return res.status(500).json({ status: 'fail', message: 'Failed to update whitelist IP' });

            exec('sudo kill -s USR1 $(pidof 3proxy)', (err) => {
                if (err) return res.status(500).json({ status: 'fail', message: 'Failed to reconfigure 3proxy' });
                res.json({ status: 'success', message: 'Whitelist IP updated successfully', authUser, whitelistIp });
            });
        });
    });
});

app.post('/delete-proxy', (req, res) => {
    const { authUser, apikey } = req.body;

    if (apikey != "eBSNhaSfFzzHHSx2E9cJTubNT") {
        return res.status(400).json({ status: 'fail', message: 'Invalid API KEY' });
    }

    fs.readFile(CONFIG_FILE, 'utf8', (err, data) => {
        if (err) return res.status(500).json({ status: 'fail', message: 'Failed to read config file' });

        const newData = data.split('\n').filter(line => !line.includes(`allow ${authUser}`) && !line.includes(`users ${authUser}:CL:`)).join('\n');

        fs.writeFile(CONFIG_FILE, newData, (err) => {
            if (err) return res.status(500).json({ status: 'fail', message: 'Failed to delete proxy' });

            exec('sudo kill -s USR1 $(pidof 3proxy)', (err) => {
                if (err) return res.status(500).json({ status: 'fail', message: 'Failed to reconfigure 3proxy' });
                res.json({ status: 'success', message: 'Proxy deleted successfully', authUser });
            });
        });
    });
});

app.get('/list-proxies', (req, res) => {
    fs.readFile(CONFIG_FILE, 'utf8', (err, configData) => {
        if (err) return res.status(500).json({ status: 'fail', message: 'Failed to read config file' });

        const proxies = [];
        let currentProxy = null;

        const configLines = configData.split('\n');

        for (let i = 0; i < configLines.length; i++) {
            const line = configLines[i];
            if (line.startsWith('users ')) {
                if (currentProxy) {
                    proxies.push(currentProxy);
                }
                const userLine = line.split(':');
                const username = userLine[0].split(' ')[1];
                const password = userLine[2];
                currentProxy = { users: [{ username, password }] };
            } else if (line.startsWith('allow ') && currentProxy) {
                const allowLine = line.split(' ');
                const username = allowLine[1];
                if (allowLine[2]) {
                    const whitelistIp = allowLine[2];
                    currentProxy.users[0].whitelistIp = whitelistIp;
                }
            } else if (line.startsWith('external ') && currentProxy) {
                const externalLine = line.split(' ');
                currentProxy.external = externalLine[1];
            } else if ((line.startsWith('proxy -n -p') || line.startsWith('https -n -p') || line.startsWith('socks -n -p')) && currentProxy) {
                const proxyLine = line.split('-p');
                currentProxy.port = proxyLine[1].split(' ')[0];
                proxies.push(currentProxy);
                currentProxy = null;
            }
        }

        if (currentProxy) {
            proxies.push(currentProxy);
        }

        res.json({ status: 'success', proxies });
    });
});

app.get('/get-user-traffic', (req, res) => {
    fs.readFile(LOG_FILE, 'utf8', (err, data) => {
        if (err) return res.status(500).json({ status: 'fail', message: 'Failed to read log file' });

        const userTraffic = {};
        const lines = data.split('\n');

        lines.forEach(line => {
            const match = line.match(/PROXY\.\d+\s+\S+\s+(\S+)\s+/);
            if (match) {
                const user = match[1];
                const parts = line.split(' ');
                const bytesSent = parseInt(parts[8], 10);
                const bytesReceived = parseInt(parts[9], 10);

                if (!userTraffic[user]) {
                    userTraffic[user] = 0;
                }
                userTraffic[user] += bytesSent + bytesReceived;
            }
        });

        const result = Object.entries(userTraffic).map(([user, traffic]) => ({
            user,
            trafficMB: (traffic / (1024 * 1024)).toFixed(2)
        }));

        res.json({ status: 'success', userTraffic: result });
    });
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
