@echo off

Rscript install\install.R

set domain=
set startDate=
set endDate=
set cache=false
set args=()
set nArgs=0

:loop
if not "%~1" == "" (
	set or_=
	if "%~1" == "-d" set or_=true
    if "%~1" == "--domain" set or_=true
	if defined or_ (
        set domain=%~2
        shift & shift
		goto :loop
    )
	set or_=
    if "%~1" == "-s" set or_=true
    if "%~1" == "--startDate" set or_=true
	if defined or_ (
        set startDate=%~2
        shift & shift
		goto :loop
    )
	set or_=
	if "%~1" == "-e" set or_=true
    if "%~1" == "--endDate" set or_=true
	if defined or_ (
        set endDate=%~2
		shift & shift
		goto :loop
    )
	set or_=
	if "%~1" == "-c" set or_=true
    if "%~1" == "--cache" set or_=true
	if defined or_ (
        set cache=true
		shift
		goto :loop
    )
	set args[%nArgs%]=%~1
	set /a nArgs+=1
    shift
    goto :loop
)

if not "%args[0]%" == "" (
    set startDate="%args[0]%"
)

if not "%args[1]%" == "" (
    set endDate="%args[1]%"
)

if not "%args[2]%" == "" (
    set domain="%args[2]%"
)

set or_=
if "%startDate%" == "" set or_=true
if "%endDate%" == "" set or_=true
if defined or_ (
    echo ERROR: A startDate and endDate must be provided
	goto :eof
)

:: Run Java Application to generate all the CSVs
if not "%cache%" == "true" (
    echo Computing Centralities
    java -jar -Xmx1024M Network.jar %startDate% %endDate% %domain%
)

:: Do some analysis using R, and create some charts
echo Analyzing Data
Rscript Network.R %startDate% %endDate% 2> NUL 1> NUL
