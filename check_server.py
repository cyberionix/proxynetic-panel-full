import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    "cd /var/www/vhosts/proxynetic.com/my.proxynetic.com && php artisan tinker --execute=\"\\DB::select('DESCRIBE user_sessions');\" 2>&1 | head -50",
    "cd /var/www/vhosts/proxynetic.com/my.proxynetic.com && php artisan tinker --execute=\"echo \\DB::table('user_sessions')->count();\" 2>&1",
    "cd /var/www/vhosts/proxynetic.com/my.proxynetic.com && grep -c 'sessionsTable' resources/views/admin/pages/users/details/index.blade.php 2>&1",
    "cd /var/www/vhosts/proxynetic.com/my.proxynetic.com && git log --oneline -5 2>&1",
    "cd /var/www/vhosts/proxynetic.com/my.proxynetic.com && cat .env | grep -E 'DB_|APP_ENV|APP_DEBUG' 2>&1",
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
