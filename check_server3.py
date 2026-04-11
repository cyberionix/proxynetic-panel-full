import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

commands = [
    # Check auth config
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/config/auth.php | head -80",
    # Check nginx error logs for 500 errors
    "grep -E '500|user-sessions' /var/www/vhosts/proxynetic.com/my.proxynetic.com/storage/logs/laravel-2026-04-11.log | head -20",
    # Check if there are any session-related errors in last few days
    "grep -i 'user.session' /var/www/vhosts/proxynetic.com/my.proxynetic.com/storage/logs/laravel-2026-04-10.log 2>/dev/null | tail -10",
    # Check the Kernel middleware
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/app/Http/Kernel.php 2>/dev/null",
    # Check VerifyCsrfToken exceptions
    "cat /var/www/vhosts/proxynetic.com/my.proxynetic.com/app/Http/Middleware/VerifyCsrfToken.php 2>/dev/null",
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
