# ── Stage 1 : Build de l'application Vue ──
FROM node:22-alpine AS builder

RUN corepack enable && corepack prepare pnpm@latest --activate

WORKDIR /app

# Copier les fichiers de dépendances en premier (cache Docker optimal)
COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile

# Copier le code source et builder
COPY . .
RUN pnpm run build

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