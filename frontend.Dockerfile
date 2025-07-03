# Stage 1: Build the Vue.js application
FROM node:18-alpine as build-stage

# Set working directory
WORKDIR /app

# Copy package.json and package-lock.json (or yarn.lock)
COPY vue/package.json vue/package-lock.json* ./

# Install dependencies
RUN npm install

# Copy the rest of the Vue application code
COPY vue/ .

# Set the API base URL from an environment variable during build
# This will be baked into the static files.
# Alternatively, this can be set at runtime via a config file loaded by Vue.
ARG VITE_API_BASE_URL=/api
ENV VITE_API_BASE_URL=${VITE_API_BASE_URL}

# Build the application
RUN npm run build

# Stage 2: Serve the built application with Nginx
FROM nginx:stable-alpine
LABEL maintainer="Jules"

# Copy built assets from the build stage
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copy custom Nginx configuration for Vue app (handles SPA routing)
COPY docker/nginx/vue-default.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
