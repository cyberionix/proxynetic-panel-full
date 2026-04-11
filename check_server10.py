import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    # Session config
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/config/session.php",
    # Check the full admin flow from the processed log
    "grep 'netAdmin' /var/www/vhosts/proxynetic.com/logs/my.proxynetic.com/access_ssl_log.processed | grep '176.42.132.233' | tail -40",
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
