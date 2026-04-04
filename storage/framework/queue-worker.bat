@echo off
cd /d "C:\Users\Administrator\Desktop\myproxyneticcom\proxynetic-app-main\proxynetic-app-main"
"C:\xampp\php\php.exe" "C:\Users\Administrator\Desktop\myproxyneticcom\proxynetic-app-main\proxynetic-app-main\artisan" queue:work --stop-when-empty --timeout=60 > "C:\Users\Administrator\Desktop\myproxyneticcom\proxynetic-app-main\proxynetic-app-main\storage\logs/queue-worker.log" 2>&1
