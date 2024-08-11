BLACK="\033[0;30m"
BLUE="\033[0;34m"
BLUE_BOLD="\033[1;34m"
CYAN="\033[0;36m"
CYAN_BOLD="\033[1;36m"
GRAY_DARK="\033[1;30m"
GREEN="\033[0;32m"
GREEN_BOLD="\033[1;32m"
PURPLE="\033[0;35m"
PURPLE_BOLD="\033[1;35m"
RESET="\033[00m"
RED="\033[0;31m"
RED_BOLD="\033[1;31m"
WHITE="\033[0;37m"
WHITE_BOLD="\033[1;37m"
YELLOW="\033[0;33m"
YELLOW_BOLD="\033[1;33m"

# BG_BLACK="\033[33;40m"
WHITE_ON_RED="\033[37;41m"
BG_RED="41"
BG_GREEN="42"
BG_YELLOW="43"
BG_BLUE="44"
BG_MAGENTA="45"
BG_CYAN="46"
BG_LIGHT_GREY="47"

cecho () {
    color=$(get-color $1 $3)

    echo -e "${!color}$2${RESET}"
}

get-color () {
    bold=""

    if [ "$2" != "" ]; then
        bold="_BOLD"
    fi
    
    echo "${1^^}$bold"
}
