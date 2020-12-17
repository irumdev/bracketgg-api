FROM nginx
ARG uploadMaxSize=5
COPY ./nginx/nginx.conf /etc/nginx/conf.d/default.conf
RUN apt-get -y update && apt-get -y install nginx-extras && \
    sed -i "s/uploadMaxSize/${uploadMaxSize}M/g" /etc/nginx/conf.d/default.conf
COPY . /var/www

EXPOSE 4663

CMD ["nginx", "-g", "daemon off;"]
