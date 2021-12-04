FROM nginx:1.19.6

RUN mkdir -p /etc/nginx/templates \
    mkdir -p /tmp/nginx

COPY ./docker/nginx/nginx.conf /etc/nginx/
COPY ./docker/nginx/default.conf /etc/nginx/
COPY ./docker/nginx/templates/* /etc/nginx/templates/

ARG NGINX_SYMFONY_SERVER_NAME='127.0.0.1'

CMD ["nginx"]

EXPOSE 80
EXPOSE 443