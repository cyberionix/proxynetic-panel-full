import subprocess, sys

host = "38.210.77.40"
port = "4383"
user = "root"
password = "NInKz7a8oZkx"
commands = "cd /var/www/vhosts/proxynetic.com/my.proxynetic.com && git pull"

try:
    import paramiko
except ImportError:
    subprocess.check_call([sys.executable, "-m", "pip", "install", "paramiko", "-q"])
    import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(host, port=int(port), username=user, password=password, timeout=15)
stdin, stdout, stderr = client.exec_command(commands)
print(stdout.read().decode())
err = stderr.read().decode()
if err:
    print("STDERR:", err)
client.close()
