FROM nginx:1.19.6

RUN mkdir -p /etc/nginx/templates \
    mkdir -p /tmp/nginx

COPY ./.docker/nginx/nginx.conf /etc/nginx/
COPY ./.docker/nginx/templates/* /etc/nginx/templates/

CMD ["nginx"]

EXPOSE 80
EXPOSE 443