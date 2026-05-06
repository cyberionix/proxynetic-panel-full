import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    # Route service provider
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/app/Providers/RouteServiceProvider.php 2>/dev/null",
    # Check for 419/500 errors in the access log
    "tail -500 /var/log/nginx/my.proxynetic.com.access.log 2>/dev/null | grep 'user-sessions' | tail -10",
    # Or check apache
    "tail -500 /var/www/vhosts/proxynetic.com/logs/access_ssl_log 2>/dev/null | grep 'user-sessions' | tail -10",
    # Try to reproduce the error
    "/opt/plesk/php/8.3/bin/php /var/www/vhosts/proxynetic.com/my.proxynetic.com/artisan tinker --execute=\"echo App\\\\Models\\\\UserSession::count();\" 2>&1",
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
