FROM nginx:alpine

RUN mkdir -p /var/www/public
COPY ./api-docs/ /var/www/public
COPY ./infra/api-docs/nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 9968
CMD ["nginx", "-g", "daemon off;"]
