import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    # Full admin flow today - sorted
    "grep 'netAdmin' /var/www/vhosts/proxynetic.com/logs/my.proxynetic.com/access_ssl_log",
    # Check the auth config providers section
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/config/auth.php",
    # Check AuthController loginPost and userAccountLogin
    "grep -A 30 'function loginPost\\|function userAccountLogin' /var/www/vhosts/proxynetic.com/my.proxynetic.com/app/Http/Controllers/Admin/AuthController.php",
]

for cmd in commands:
    print(f"\n=== CMD: {cmd[:80]}... ===")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode()
    err = stderr.read().decode()
    if out:
        print(out[:5000])
    if err:
        print("STDERR:", err[:500])

client.close()
