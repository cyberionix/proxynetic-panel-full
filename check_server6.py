import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    # Find all access logs
    "find /var/www/vhosts/proxynetic.com/logs -name '*access*' -type f 2>/dev/null",
    # Check access log for user-sessions ajax calls
    "grep 'user-sessions' /var/www/vhosts/proxynetic.com/logs/access_ssl_log 2>/dev/null | tail -10",
    # Check ALL logs for user-sessions
    "grep -r 'user-sessions' /var/www/vhosts/proxynetic.com/logs/ 2>/dev/null | tail -10",
    # Try to reproduce the issue with a direct PHP call
    """/opt/plesk/php/8.3/bin/php -r "
require '/var/www/vhosts/proxynetic.com/my.proxynetic.com/vendor/autoload.php';
\$app = require '/var/www/vhosts/proxynetic.com/my.proxynetic.com/bootstrap/app.php';
\$kernel = \$app->make('Illuminate\Contracts\Http\Kernel');
echo 'App loaded OK';
" 2>&1""",
    # Check the nginx/apache config for user-sessions
    "ls /var/www/vhosts/proxynetic.com/logs/",
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
