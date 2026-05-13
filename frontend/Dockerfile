FROM node:20-alpine AS vite-app

WORKDIR /app
COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build

FROM nginx:stable-alpine

WORKDIR /usr/share/nginx/

RUN rm -rf html && \
    mkdir html

WORKDIR /

COPY ./nginx/nginx.conf /etc/nginx/nginx.conf
COPY --from=vite-app /app/dist /usr/share/nginx/html

ENTRYPOINT ["nginx", "-g", "daemon off;"]