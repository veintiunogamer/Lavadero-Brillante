# Lavadero Brillante

**Lavadero Brillante** es una **aplicación web para agendar y vender servicios de lavado de coches de forma rápida y sencilla**, desarrollada con Laravel y tecnologías web modernas. :contentReference[oaicite:1]{index=1}

---

## 📌 Descripción

Esta app web permite a los usuarios:

- 📅 **Agendar servicios de lavado de coches**
- 💳 **Vender y gestionar servicios**
- 🧽 **Ofrecer una experiencia rápida y eficiente**

Ideal para negocios de lavado de coches que requieren una herramienta digital para organizar sus servicios y clientes. :contentReference[oaicite:2]{index=2}

---

## 🚀 Tecnologías utilizadas

El proyecto utiliza las siguientes tecnologías principales:

- 🛠️ **Laravel** como framework PHP backend  
- 📦 **Composer** para gestión de dependencias  
- 🧠 **JavaScript, CSS y Blade** para frontend  
- 🐳 **Docker & Docker Compose** para contenerización  
- 💻 Configuración de entorno y servicios adicionales  

---

## 📁 Estructura del proyecto

La estructura de carpetas del proyecto es la siguiente (resumen):

├── app/
├── bootstrap/
├── config/
├── database/
├── nginx/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── .env.example
├── Dockerfile
├── docker-compose.yml
├── composer.json
├── package.json


---

## 🧩 Requisitos previos

Antes de instalar y ejecutar el proyecto, asegúrate de tener instalados:

- 🐳 Docker
- 🐋 Docker Compose
- 🐘 PHP (si no usas Docker)
- 📦 Composer
- 📀 Node.js y NPM/Yarn  

---

## 🔧 Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/veintiunogamer/Lavadero-Brillante.git


2. **Copiar el archivo de entorno**

cp .env.example .env


3. **Configurar variables de entorno**
Edita el archivo .env con tus credenciales y configuración.

4. **Levantar contenedores**

docker compose up -d


5. **Instalar dependencias**

composer install
npm install
npm run dev


6. **Ejecutar migraciones**

php artisan migrate


## **▶️ Uso**

Una vez levantado el proyecto, podrás acceder desde tu navegador en:

http://localhost


Dependiendo de tu configuración de Docker o entorno local.

## **📦 Docker**

Este proyecto incluye configuración de Docker para facilitar el despliegue y desarrollo local. Solo debes ejecutar:

docker compose up -d


Esto levantará todos los servicios necesarios automáticamente.

## **🧪 Pruebas**

Ejecuta las pruebas automatizadas con:

php artisan test

