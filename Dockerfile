# ------------------------------------------------------------------
# Surethan S portfolio — production image
# PHP 8.3 + Apache, docroot locked to public/, hardened php.ini
# ------------------------------------------------------------------
FROM php:8.3-apache

# Apache modules the site relies on (.htaccess: headers, rewrite, caching)
RUN a2enmod headers rewrite expires

# Point the docroot at public/ so app/ and var/ are never web-servable
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
      /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n  AllowOverride All\n  Require all granted\n</Directory>\n' \
      > /etc/apache2/conf-available/portfolio.conf \
    && a2enconf portfolio \
    && sed -ri 's/^ServerTokens .*/ServerTokens Prod/; s/^ServerSignature .*/ServerSignature Off/' \
      /etc/apache2/conf-enabled/security.conf

# PHP hardening
RUN { \
      echo 'expose_php = 0'; \
      echo 'display_errors = 0'; \
      echo 'display_startup_errors = 0'; \
      echo 'log_errors = 1'; \
      echo 'error_log = /dev/stderr'; \
      echo 'allow_url_include = 0'; \
      echo 'session.use_strict_mode = 1'; \
      echo 'session.cookie_httponly = 1'; \
      echo 'session.cookie_samesite = Lax'; \
      echo 'memory_limit = 128M'; \
      echo 'post_max_size = 1M'; \
      echo 'upload_max_filesize = 1M'; \
      echo 'max_execution_time = 15'; \
    } > /usr/local/etc/php/conf.d/hardening.ini

# Mail delivery: route PHP mail() through Gmail SMTP via msmtp.
# The Gmail App Password is injected at runtime as $SMTP_PASS — never baked in.
RUN apt-get update \
    && apt-get install -y --no-install-recommends msmtp msmtp-mta ca-certificates \
    && rm -rf /var/lib/apt/lists/* \
    && printf '%s\n' \
      'defaults' \
      'auth on' \
      'tls on' \
      'tls_starttls on' \
      'tls_trust_file /etc/ssl/certs/ca-certificates.crt' \
      'logfile /dev/stderr' \
      '' \
      'account gmail' \
      'host smtp.gmail.com' \
      'port 587' \
      'from surethan37@gmail.com' \
      'user surethan37@gmail.com' \
      'passwordeval "printenv SMTP_PASS"' \
      '' \
      'account default : gmail' \
      > /etc/msmtprc \
    && chmod 644 /etc/msmtprc \
    && echo 'sendmail_path = "/usr/bin/msmtp -t -i"' \
      > /usr/local/etc/php/conf.d/mail.ini

COPY app/ /var/www/html/app/
COPY public/ /var/www/html/public/

# Writable state dir (secret key, rate-limit counters, message log)
RUN mkdir -p /var/www/html/var \
    && chown -R www-data:www-data /var/www/html/var \
    && chmod 750 /var/www/html/var

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -fsS http://localhost/ >/dev/null || exit 1
