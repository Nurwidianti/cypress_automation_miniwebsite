@echo off
Start-Process php -ArgumentList "%~dp0artisan serve --port=8000 --host=0.0.0.0" -WindowStyle Hidden
