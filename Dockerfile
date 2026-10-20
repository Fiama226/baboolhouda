# =============================================================================
# TV Baaboul Houda — full site image (WordPress + custom theme + core plugin)
# Build:  docker build -t tv-baaboul-houda .
# Run:    docker compose up -d          (see docker-compose.yml)
#
# WordPress core is NOT committed to git; it is vendored from the pinned tag
# in sources.json (see scripts/vendor-core.sh) and fetched here if absent.
# =============================================================================
FROM wordpress:7.1-php8.3-apache AS site

# Remove the bundled theme (we ship our own) and its readme clutter.
RUN rm -rf /var/www/html/wp-content/themes/twenty* /var/www/html/readme.html

# Copy the site tree (theme + plugins + wp-config-sample + .htaccess).
COPY site/ /var/www/html/
COPY sources.json /tmp/sources.json

# Fetch the pinned WordPress core if not already vendored in the tree.
RUN if [ ! -f /var/www/html/wp-settings.php ]; then \
        echo ">> Vendoring WordPress core (pinned by sources.json)"; \
        ref="$(node -e 'console.log(JSON.parse(require("fs").readFileSync("/tmp/sources.json","utf8")).wordpress_core.ref)' 2>/dev/null \
            || php -r 'echo json_decode(file_get_contents("/tmp/sources.json"),true)["wordpress_core"]["ref"];')"; \
        curl -fsSL "https://github.com/WordPress/WordPress/archive/refs/tags/${ref}.tar.gz" -o /tmp/wp.tgz; \
        tar -xzf /tmp/wp.tgz -C /var/www/html --strip-components=1; \
        rm -f /tmp/wp.tgz; \
    fi && \
    chown -R www-data:www-data /var/www/html

# PHP tuning (uploads, API timeouts, mail-friendly settings).
RUN { \
      echo 'memory_limit = 256M'; \
      echo 'upload_max_filesize = 64M'; \
      echo 'post_max_size = 64M'; \
      echo 'max_execution_time = 120'; \
      echo 'output_buffering = On'; \
    } > /usr/local/etc/php/conf.d/baaboul.ini

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
  CMD curl -fsS http://localhost/ >/dev/null || exit 1

CMD ["apache2-foreground"]
