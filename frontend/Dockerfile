FROM nginx:alpine

# Copy custom Nginx configuration to proxy /api/ to internal api container
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy web files to Nginx html directory
COPY . /usr/share/nginx/html

EXPOSE 80
