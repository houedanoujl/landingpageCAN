#!/usr/bin/env bash
#
# Auto-installe les dépendances Composer au démarrage du conteneur si le
# dossier vendor/ est absent (ex: premier lancement, clone frais).
# Ce script est SOURCÉ par l'entrypoint webdevops (/entrypoint.d/*.sh),
# donc on évite tout `exit` et on isole le `cd` dans un sous-shell.

if [ ! -f /app/vendor/autoload.php ]; then
    echo "[entrypoint] vendor/autoload.php absent — exécution de composer install..."
    ( cd /app && composer install --no-interaction --prefer-dist --no-progress )
    echo "[entrypoint] composer install terminé."
else
    echo "[entrypoint] Dépendances Composer déjà présentes — étape ignorée."
fi
