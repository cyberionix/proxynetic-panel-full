@echo off
cd /d "C:\Users\Administrator\Desktop\myproxyneticcom\proxynetic-app-main\proxynetic-app-main"
"C:\xampp\php\php.exe" "C:\Users\Administrator\Desktop\myproxyneticcom\proxynetic-app-main\proxynetic-app-main\artisan" schedule:work > "C:\Users\Administrator\Desktop\myproxyneticcom\proxynetic-app-main\proxynetic-app-main\storage\logs/scheduler-worker.log" 2>&1
