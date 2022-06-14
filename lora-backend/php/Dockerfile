FROM php:7.3.5-fpm-alpine3.9
RUN curl -fsSL -o /tmp/msodbcsql17_17.5.2.2-1_amd64.apk https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.5.2.2-1_amd64.apk \
    && curl -fsSL -o /tmp/mssql-tools_17.5.2.1-1_amd64.apk https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/mssql-tools_17.5.2.1-1_amd64.apk \
    && apk add --allow-untrusted /tmp/msodbcsql17_17.5.2.2-1_amd64.apk \
    && apk add --allow-untrusted /tmp/mssql-tools_17.5.2.1-1_amd64.apk
RUN apk update; \
    apk upgrade; \
    apk add autoconf g++ libmcrypt libmcrypt-dev libzip libzip-dev make oniguruma oniguruma-dev openldap-dev openssl openssl-dev unixodbc-dev;
RUN pecl install sqlsrv-5.8.1 \
    && pecl install pdo_sqlsrv-5.8.1 \
    && pecl install xdebug \
    && pecl install mcrypt \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install \
      ldap \
      zip \
    && docker-php-ext-enable \
      sqlsrv \
      pdo_sqlsrv \
      mcrypt \
      opcache \
      xdebug
