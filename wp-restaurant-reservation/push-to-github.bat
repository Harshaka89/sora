@echo off
cd /d %~dp0
git add .
git commit -m "Full project update"
git push
pause
