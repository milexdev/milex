#!/bin/bash

setup_milex() {
    [ -z "${MILEX_URL}" ] && MILEX_URL="https://${DDEV_HOSTNAME}/index_dev.php"
    [ -z "${PHPMYADMIN_URL}" ] && PHPMYADMIN_URL="https://${DDEV_HOSTNAME}:8037"
    [ -z "${MAILHOG_URL}" ] && MAILHOG_URL="https://${DDEV_HOSTNAME}:8026"

    printf "Installing Milex Composer dependencies...\n"
    composer install

    cp ./.ddev/local.config.php.dist ./app/config/local.php
    cp ./.env.dist ./.env

    printf "Installing Milex...\n"
    php bin/console milex:install "${MILEX_URL}" \
        --mailer_from_name="DDEV" --mailer_from_email="milex@ddev.local" \
        --mailer_transport="smtp" --mailer_host="localhost" --mailer_port="1025"
    php bin/console cache:warmup --no-interaction --env=dev

    printf "Enabling plugins...\n"
    php bin/console milex:plugins:reload

    tput setaf 2
    printf "All done! Here's some useful information:\n"
    printf "🔒 The default login is admin/milex\n"
    printf "🌐 To open the Milex instance, go to ${MILEX_URL} in your browser.\n"
    printf "🌐 To open PHPMyAdmin for managing the database, go to ${PHPMYADMIN_URL} in your browser.\n"
    printf "🌐 To open MailHog for seeing all emails that Milex sent, go to ${MAILHOG_URL} in your browser.\n"
    printf "🚀 Run \"ddev exec composer test\" to run PHPUnit tests.\n"
    printf "🚀 Run \"ddev exec bin/console COMMAND\" (like milex:segments:update) to use the Milex CLI. For an overview of all available CLI commands, go to https://mau.tc/cli\n"
    printf "🔴 If you want to stop the instance, simply run \"ddev stop\".\n"
    tput sgr0
}

# Check if the user has indicated their preference for the Milex installation
# already (DDEV-managed or self-managed)
if ! test -f ./.ddev/milex-preference
then
    tput setab 3
    tput setaf 0
    printf "Do you want us to set up the Milex instance for you with the recommended settings for DDEV?\n"
    printf "If you answer \"no\", you will have to set up the Milex instance yourself."
    tput sgr0
    printf "\nAnswer [yes/no]: "
    read MILEX_PREF

    if [ $MILEX_PREF == "yes" ] || [ -n $GITPOD_HEADLESS ];
    then
        printf "Okay, setting up your Milex instance... 🚀\n"
        echo "ddev-managed" > ./.ddev/milex-preference
        setup_milex
    else
        printf "Okay, you'll have to set up the Milex instance yourself. That's what pros do, right? Good luck! 🚀\n"
        echo "unmanaged" > ./.ddev/milex-preference
    fi
fi
