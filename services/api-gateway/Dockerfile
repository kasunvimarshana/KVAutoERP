# ─── Build stage: validate nginx config ──────────────────────────────────────
FROM nginx:1.25-alpine AS builder

COPY nginx.conf /etc/nginx/conf.d/default.conf

# Validate configuration syntax at build time so broken configs fail fast.
RUN nginx -t -c /etc/nginx/nginx.conf

# ─── Runtime stage ────────────────────────────────────────────────────────────
FROM nginx:1.25-alpine

LABEL maintainer="platform-team@saas.example.com" \
      org.opencontainers.image.title="SaaS API Gateway" \
      org.opencontainers.image.description="Nginx-based API gateway for the SaaS Inventory Platform" \
      org.opencontainers.image.version="1.0.0"

# Install curl for the HEALTHCHECK and remove default site config.
RUN apk add --no-cache curl \
 && rm -f /etc/nginx/conf.d/default.conf

# Copy validated config from the builder stage.
COPY --from=builder /etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

# Write a production-tuned nginx.conf using a heredoc-safe approach.
COPY nginx-main.conf /etc/nginx/nginx.conf

# Ensure log directories exist and are writable by the nginx user.
RUN mkdir -p /var/log/nginx \
 && chown -R nginx:nginx /var/log/nginx \
 && chmod -R 755 /var/log/nginx

# Forward Nginx logs to Docker log driver.
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 80 443

# Graceful shutdown: give workers time to drain active connections.
STOPSIGNAL SIGQUIT

HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

CMD ["nginx", "-g", "daemon off;"]
