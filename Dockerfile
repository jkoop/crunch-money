FROM alpine
WORKDIR /app
COPY . .
RUN mkdir /.npm && \
	chown -R 33 . /.npm && \
	apk add composer php83-dom php83-pdo_sqlite npm && \
	mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views && \
	touch database/database.sqlite && \
	composer install --no-dev --ignore-platform-reqs && \
	npm ci --no-dev --no-audit --no-fund && \
	npm run build && \
	mkdir /build /build/database /build/resources && \
	cp -r app artisan bootstrap composer.json config public routes start.sh storage vendor /build && \
	cp -r database/migrations /build/database && \
	cp -r resources/views /build/resources && \
	ln -s /var/www/public /build/html

FROM trafex/php-nginx:3.6.0
USER root
RUN rm -fr /var/www/html && \
	apk add php83-calendar php83-pdo_sqlite
COPY --from=0 --chown=nobody /build /var/www
WORKDIR /var/www
RUN mkdir /storage && chmod 777 /storage
USER nobody
EXPOSE 8080
ENV DB_DATABASE=/storage/database.sqlite
ENV LOG_CHANNEL=stderr
CMD ["/var/www/start.sh"]
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/up || exit 1