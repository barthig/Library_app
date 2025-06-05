FROM postgres:16-alpine

# Update system packages to reduce vulnerabilities
RUN apk update && apk upgrade --no-cache

# Set non-sensitive environment variables using key=value format
ENV POSTGRES_DB=db
ENV POSTGRES_USER=docker

# Do not set POSTGRES_PASSWORD here; pass it securely at runtime using Docker secrets or environment variables

COPY . /app/database