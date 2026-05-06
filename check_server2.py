import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    "which php8.1 || which php8.2 || which php8.3 || find /opt -name 'php' -type f 2>/dev/null | head -5 || ls /opt/plesk/php/*/bin/php 2>/dev/null",
    "mysql -u admin_proxynetic_app -p'852456asd000' admin_proxynetic_app -e 'DESCRIBE user_sessions;' 2>&1",
    "mysql -u admin_proxynetic_app -p'852456asd000' admin_proxynetic_app -e 'SELECT COUNT(*) as cnt FROM user_sessions WHERE deleted_at IS NULL;' 2>&1",
    "mysql -u admin_proxynetic_app -p'852456asd000' admin_proxynetic_app -e 'SELECT * FROM user_sessions WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 3;' 2>&1",
]

for cmd in commands:
    print(f"\n=== CMD: {cmd[:80]}... ===")
    stdin, stdout, stderr = client.exec_command(cmd)
    out = stdout.read().decode()
    err = stderr.read().decode()
    if out:
        print(out)
    if err:
        print("STDERR:", err)

client.close()
