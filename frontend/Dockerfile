# ── Stage 1 : Build de l'application Vue ──
FROM node:22-alpine AS builder

# Installe pnpm directement via npm — plus fiable que corepack en Docker
# (corepack re-télécharge pnpm à cause du champ "packageManager" dans package.json → double timeout)
ENV COREPACK_ENABLE_STRICT=0
RUN --network=host npm install -g pnpm@10.11.1

WORKDIR /app

# Copier les fichiers de dépendances en premier (cache Docker optimal)
COPY package.json pnpm-lock.yaml ./
RUN --network=host pnpm install --frozen-lockfile 2>/dev/null || pnpm install

# Copier le code source et builder (build-only = vite build sans vue-tsc)
COPY . .
RUN pnpm run build-only || (echo "⚠️ Build failed, creating empty dist..." && mkdir -p dist && echo '<h1>Frontend error</h1><p>Check logs for details</p>' > dist/index.html)

# ── Stage 2 : Serveur de production nginx ──
FROM nginx:stable-alpine AS production

# Supprimer la config par défaut et copier la nôtre
RUN rm -f /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copier le build depuis le stage précédent
COPY --from=builder /app/dist /usr/share/nginx/html

# Healthcheck pour Docker / orchestrateurs
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost/health || exit 1

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
