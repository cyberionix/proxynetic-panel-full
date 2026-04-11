import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=15)

stdin, stdout, stderr = client.exec_command(
    'grep -iE "user.?session|session.?table" /var/www/vhosts/proxynetic.com/my.proxynetic.com/storage/logs/laravel-2026-04-11.log | tail -30'
)
print(stdout.read().decode())

stdin, stdout, stderr = client.exec_command(
    'tail -100 /var/www/vhosts/proxynetic.com/my.proxynetic.com/storage/logs/laravel-2026-04-11.log'
)
print(stdout.read().decode())

client.close()
