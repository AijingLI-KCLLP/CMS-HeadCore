#!/bin/bash
set -e

echo "Attente de PostgreSQL..."
until pg_isready -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER"; do
  sleep 1
done

echo "Création du schéma..."
php /var/www/html/bin/console.php create:schema

exec apache2-foreground