# ── Stage 1 : Build de l'application Vue ──
FROM node:22-alpine AS builder

# Installer pnpm directement (plus fiable que corepack en Docker)
# --network=host : utilise le réseau de l'hôte pour éviter les timeouts Docker bridge
ENV COREPACK_ENABLE_STRICT=0
# Augmenter les timeouts pour connexions lentes (npm)
ENV NPM_CONFIG_FETCH_TIMEOUT=600000
ENV NPM_CONFIG_FETCH_RETRIES=5
RUN --network=host npm install -g pnpm@10.11.1

WORKDIR /app

# Configurer les timeouts pnpm via .npmrc (pnpm ne supporte pas --network-timeout en CLI)
RUN echo "network-timeout=600000" > .npmrc && \
    echo "fetch-retries=5" >> .npmrc

# Copier les fichiers de dépendances en premier (cache Docker optimal)
COPY package.json pnpm-lock.yaml ./
RUN --network=host pnpm install --frozen-lockfile

# Copier le code source et builder
COPY . .
RUN pnpm run build-only

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
