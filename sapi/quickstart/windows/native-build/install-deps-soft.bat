@echo off

rem chcp 65001

setlocal
rem show current file location
echo %~dp0
cd /d %~dp0
cd /d .\..\..\..\..\

set "__PROJECT__=%cd%"
echo %cd%


rem silent installation msi
rem msiexec /i strawberry-perl-5.38.2.2-64bit.msi /quiet

msiexec /i strawberry-perl-5.38.2.2-64bit.msi  /passive

Git-2.45.1-64-bit.exe /install /passive /norestart

set "PATH=%PATH%;%__PROJECT__%\php\;%__PROJECT__%\nasm\;C:\Strawberry\perl\bin"
echo %PATH%


perl -v
php -v
nasm -v

endlocal
