import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/app/Http/Middleware/Authenticate.php",
    # Check if admin GET requests also return 401 or only POST
    "grep 'netAdmin' /var/www/vhosts/proxynetic.com/logs/my.proxynetic.com/access_ssl_log | grep -v '\" 200 ' | grep -v '\" 302 ' | head -20",
    # Check what 401 responses look like - is it a redirect?
    "grep '\" 401 ' /var/www/vhosts/proxynetic.com/logs/my.proxynetic.com/access_ssl_log | head -5",
    # Check the full request flow for admin page load today
    "grep '04:06:' /var/www/vhosts/proxynetic.com/logs/my.proxynetic.com/access_ssl_log | grep 'netAdmin' | head -20",
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
