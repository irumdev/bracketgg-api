FROM nginx
COPY ./nginx/nginx.conf /etc/conf.d/default.conf
COPY . /var/www

EXPOSE 4663

CMD ["nginx", "-g", "daemon off;"]
