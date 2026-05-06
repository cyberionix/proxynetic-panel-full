import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/app/Http/Middleware/CheckProxyVpn.php 2>/dev/null",
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/app/Http/Middleware/UpdateLastSeen.php 2>/dev/null",
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/app/Http/Middleware/LogRequest.php 2>/dev/null",
    # Check all access logs for user-sessions
    "find /var/www/vhosts/proxynetic.com/logs -name '*.log' -exec grep -l 'user-sessions' {} \\; 2>/dev/null",
    "grep 'user-sessions' /var/www/vhosts/proxynetic.com/logs/proxy_access_ssl_log 2>/dev/null | tail -10",
    # check the actual web server error log
    "tail -30 /var/www/vhosts/proxynetic.com/logs/error_log 2>/dev/null",
]

for cmd in commands:
    print(f"\n=== CMD: {cmd[:80]}... ===")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode()
    err = stderr.read().decode()
    if out:
        print(out[:3000])
    if err:
        print("STDERR:", err[:500])

client.close()
