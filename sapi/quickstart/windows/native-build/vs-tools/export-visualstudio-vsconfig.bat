@echo off

echo %~dp0
cd %~dp0
cd ..\..\..\..\..\

set __PROJECT__=%cd%
echo %cd%

VisualStudioSetup.exe export 	--passive  --force
