# Path: docker/nginx/Dockerfile
FROM nginx:alpine

# Remove the default configuration
RUN rm /etc/nginx/conf.d/default.conf

# Copy nginx.conf from docker/nginx/ into the container as default.conf
COPY docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html
