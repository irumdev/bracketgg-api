FROM nginx
COPY ./nginx/nginx.conf /etc/nginx/nginx.conf
COPY . /var/www

EXPOSE 4663

CMD ["nginx", "-g", "daemon off;"]
