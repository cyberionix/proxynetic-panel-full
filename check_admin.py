import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('38.210.77.40', port=4383, username='root', password='NInKz7a8oZkx', timeout=30)

base = '/var/www/vhosts/proxynetic.com/my.proxynetic.com'
cmd = f"""cd {base} && /opt/plesk/php/8.2/bin/php artisan tinker --execute='echo App\\Models\\Admin::first()->email;'"""
stdin, stdout, stderr = ssh.exec_command(cmd, timeout=30)
print("Email:", stdout.read().decode().strip())
print(stderr.read().decode().strip())

ssh.close()
