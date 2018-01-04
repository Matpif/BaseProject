# Command to show filename has been modify between tow commits (eg: HEAD^^..HEAD)
git log --name-only --pretty=oneline --full-index HEAD^^..HEAD | grep -vE '^[0-9a-f]{40} ' | sort | uniq

# With copy file to upgrade another project
cp --parents `git log --name-only --pretty=oneline --full-index 5f4cd92c968e75b71225a4cba034671634d1a464..HEAD | grep -vE '^[0-9a-f]{40} ' | sort | uniq` ../Paris_v2/.

# Betwwen tow versions
git log --name-only --pretty=oneline --full-index v0.0.1..v0.0.2 | grep -vE '^[0-9a-f]{40} ' | sort | uniq



cp --parents `git log --name-only --pretty=oneline --full-index v0.0.2..v0.0.3 | grep -vE '^[0-9a-f]{40} ' | sort | uniq` ../Paris_v2/.
/!\ Attention : /!\
-> Tous les fichiers qui n'existe plus, il faut les supprimer
-> Il faut relancer la commande suivant : php composer.phar update
-> Les fichiers suivants doivent être mergés :
    - /app/etc/config.json
    - /app/etc/modules.json
    - /app/etc/override.json
    - /local/*
-> Penser à vider le cache après la mise à jour