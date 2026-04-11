import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    # Check today's access log for user-sessions AND account-login
    "grep -E 'user-sessions|account-login' /var/www/vhosts/proxynetic.com/logs/my.proxynetic.com/access_ssl_log | tail -20",
    # Check for any non-200 responses to admin ajax requests today
    "grep 'netAdmin' /var/www/vhosts/proxynetic.com/logs/my.proxynetic.com/access_ssl_log | grep -vE '\" 200 |\" 302 |\" 304 ' | tail -20",
    # Check for 500 errors in today's log
    "grep '\" 500 ' /var/www/vhosts/proxynetic.com/logs/my.proxynetic.com/access_ssl_log | tail -10",
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
