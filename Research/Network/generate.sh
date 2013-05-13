#!/bin/bash

domain=""
startDate=""
endDate=""
cache=false

args=()
until [ -z "$1" ]; do
  case "$1" in
    -s|--startDate) startDate="$2"; shift 2;;
    -e|--endDate) endDate="$2"; shift 2;;
    -d|--domain) domain="$2"; shift 2 ;;
    -c) cache=true; shift 1;;
    --) shift ; break ;;
    -*) echo "invalid option $1" 1>&2 ; shift ;; # or, error and exit 1 just like getopt does
    *) args+=("$1") ; shift ;;
  esac
done

if [[ "${args[0]}" != "" ]];
then
    startDate="${args[0]}"
fi

if [[ "${args[1]}" != "" ]];
then
    endDate="${args[1]}"
fi

if [[ "${args[2]}" != "" ]];
then
    domain="${args[2]}"
fi

if [[ "$startDate" == "" || "$endDate" == "" ]];
  then
    echo "ERROR: A startDate and endDate must be provided"
    exit
fi

# Run Java Application to generate all the CSVs
if  ! $cache;
  then
    echo "Computing Centralities"
    java -jar -Xmx1024M Network.jar $startDate $endDate $domain
fi

# Do some analysis using R, and create some charts
echo "Analyzing Data"
Rscript Network.R $startDate $endDate &> /dev/null
